<?php

declare(strict_types=1);

/*
 * This file is part of the Hype package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hype;

use ArrayAccess;
use Hype\Helper\HelperInterface;
use Hype\Loader\LoaderInterface;
use Hype\Storage\FileStorage;
use Hype\Storage\Storage;
use Hype\Storage\StringStorage;
use InvalidArgumentException;
use LogicException;
use Psl;
use Psl\Hash;
use Psl\Html;
use Psl\Iter;
use Psl\Regex;
use Psl\Type;
use ReturnTypeWillChange;
use RuntimeException;

use const EXTR_SKIP;

/**
 * PhpEngine is an engine able to render PHP templates.
 *
 * @implements ArrayAccess<string, HelperInterface>
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class PhpEngine implements ArrayAccess, EngineInterface
{
    protected static array $escaperCache = [];
    protected LoaderInterface $loader;
    /**
     * @var string
     */
    protected string $current;
    /**
     * @var HelperInterface[]
     */
    protected array $helpers = [];
    /**
     * @var array<string, ?string>
     */
    protected array $parents = [];
    protected array $stack = [];
    protected string $charset = 'UTF-8';
    protected array $cache = [];
    protected array $escapers = [];
    protected array $globals = [];
    protected TemplateNameParserInterface $parser;

    /**
     * @param HelperInterface[] $helpers An array of helper instances
     */
    public function __construct(TemplateNameParserInterface $parser, LoaderInterface $loader, array $helpers = [])
    {
        $this->parser = $parser;
        $this->loader = $loader;

        $this->addHelpers($helpers);

        $this->initializeEscapers();
        foreach ($this->escapers as $context => $escaper) {
            $this->setEscaper($context, $escaper);
        }
    }

    /**
     * Adds some helpers.
     *
     * @param array<int|string, HelperInterface> $helpers An array of helper
     */
    public function addHelpers(array $helpers): void
    {
        foreach ($helpers as $alias => $helper) {
            $this->set($helper, Type\int()->matches($alias) ? null : $alias);
        }
    }

    public function set(HelperInterface $helper, string $alias = null): void
    {
        $this->helpers[$helper->getName()] = $helper;
        if (null !== $alias) {
            $this->helpers[$alias] = $helper;
        }

        $helper->setCharset($this->charset);
    }

    /**
     * Initializes the built-in escapers.
     *
     * Each function specifies a way for applying a transformation to a string
     * passed to it. The purpose is for the string to be "escaped" so it is
     * suitable for the format it is being displayed in.
     *
     * For example, the string: "It's required that you enter a username & password.\n"
     * If this were to be displayed as HTML it would be sensible to turn the
     * ampersand into '&amp;' and the apostrophe into '&aps;'. However if it were
     * going to be used as a string in JavaScript to be displayed in an alert box
     * it would be right to leave the string as-is, but c-escape the apostrophe and
     * the new line.
     *
     * For each function there is a define to avoid problems with strings being
     * incorrectly specified.
     */
    protected function initializeEscapers(): void
    {
        $this->escapers = [
            'html' =>
                /**
                 * Runs the PHP function Html\encode_special_characters on the value passed.
                 */
                function (string $value): string {
                    // Numbers and Boolean values get turned into strings which can cause problems
                    // with type comparisons (e.g. === or is_int() etc).
                    return Html\encode_special_characters($value, false, Html\Encoding::from($this->getCharset()));
                },

            'js' =>
                /**
                 * A function that escape all non-alphanumeric characters
                 * into their \xHH or \uHHHH representations.
                 *
                 * @param string $value The value to escape
                 */
                function (string $value): string {
                    if ('UTF-8' !== $this->getCharset()) {
                        $value = iconv($this->getCharset(), 'UTF-8', $value);
                    }

                    $callback = static function (array $matches): string {
                        $char = $matches[0];

                        // \xHH
                        if (!isset($char[1])) {
                            return '\\x' . substr('00' . bin2hex($char), -2);
                        }

                        // \uHHHH
                        $char = iconv('UTF-8', 'UTF-16BE', $char);

                        return '\\u' . substr('0000' . bin2hex($char), -4);
                    };

                    $value = Regex\replace_with($value, '#[^\p{L}\p{N} ]#u', $callback);

                    if ('UTF-8' !== $this->getCharset()) {
                        $value = iconv('UTF-8', $this->getCharset(), $value);
                    }

                    return $value;
                },
        ];

        self::$escaperCache = [];
    }

    /**
     * Gets the current charset.
     */
    public function getCharset(): string
    {
        return $this->charset;
    }

    /**
     * Sets the charset to use.
     */
    public function setCharset(string $charset): void
    {
        if ('UTF8' === $charset = strtoupper($charset)) {
            $charset = 'UTF-8'; // iconv on Windows requires "UTF-8" instead of "UTF8"
        }
        $this->charset = $charset;

        foreach ($this->helpers as $helper) {
            $helper->setCharset($this->charset);
        }
    }

    /**
     * Adds an escaper for the given context.
     */
    public function setEscaper(string $context, callable $escaper): void
    {
        $this->escapers[$context] = $escaper;
        self::$escaperCache[$context] = [];
    }

    /**
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException if the template does not exist
     */
    public function render(TemplateReferenceInterface|string $name, array $parameters = []): string
    {
        $storage = $this->load($name);
        $key = Hash\hash(serialize($storage), algorithm: 'sha256');
        $this->current = $key;
        $this->parents[$key] = null;

        // attach the global variables
        $parameters = array_replace($this->getGlobals(), $parameters);
        // render
        if (false === $content = $this->evaluate($storage, $parameters)) {
            throw new RuntimeException(sprintf('The template "%s" cannot be rendered.', $this->parser->parse($name)));
        }

        // decorator
        if (($parent_template = $this->parents[$key]) !== null) {
            /** @var Helper\SlotsHelper $slots */
            $slots = $this->get('slots');
            $this->stack[] = $slots->get('_content');
            $slots->set('_content', $content);

            /**
             * @var string $parent_template
             */
            $content = $this->render($parent_template, $parameters);

            $slots->set('_content', array_pop($this->stack) ?: '');
        }

        return $content;
    }

    /**
     * Loads the given template.
     *
     * @throws InvalidArgumentException if the template cannot be found
     */
    protected function load(TemplateReferenceInterface|string $name): Storage
    {
        if ($name instanceof TemplateReferenceInterface) {
            $template = $name;
        } else {
            $template = $this->parser->parse($name);
        }

        $key = $template->getLogicalName();
        if (isset($this->cache[$key])) {
            return $this->cache[$key];
        }

        $storage = $this->loader->load($template);

        if (null === $storage) {
            throw new InvalidArgumentException(sprintf('The template "%s" does not exist.', $template));
        }

        return $this->cache[$key] = $storage;
    }

    /**
     * Returns the assigned globals.
     *
     * @return array<string, mixed>
     */
    public function getGlobals(): array
    {
        return $this->globals;
    }

    /**
     * Evaluates a template.
     *
     * @throws InvalidArgumentException
     */
    protected function evaluate(Storage $template, array $parameters = []): ?string
    {
        $__evaluated_template__ = $template;
        $__evaluated_template__parameters__ = $parameters;
        unset($template, $parameters);

        if (isset($__evaluated_template__parameters__['this'])) {
            throw new InvalidArgumentException('Invalid parameter (this).');
        }
        if (isset($__evaluated_template__parameters__['view'])) {
            throw new InvalidArgumentException('Invalid parameter (view).');
        }

        // the view variable is exposed to the required file below
        $view = $this;
        if ($__evaluated_template__ instanceof FileStorage) {
            extract($__evaluated_template__parameters__, EXTR_SKIP);
            $__evaluated_template__parameters__ = null;

            ob_start();
            require $__evaluated_template__;

            $__evaluated_template__ = null;

            return ob_get_clean();
        }

        if ($__evaluated_template__ instanceof StringStorage) {
            extract($__evaluated_template__parameters__, EXTR_SKIP);
            $__evaluated_template__parameters__ = null;

            ob_start();
            eval('; ?>' . $__evaluated_template__ . '<?php ;');

            $__evaluated_template__ = null;

            return ob_get_clean();
        }

        return null;
    }

    /**
     * Gets a helper value.
     *
     * @throws Psl\Exception\InvariantViolationException if the helper is not defined
     */
    public function get(string $name): HelperInterface
    {
        Psl\invariant($this->has($name), 'The helper "%s" is not defined.', $name);

        return $this->helpers[$name];
    }

    /**
     * Returns true if the helper is defined.
     */
    public function has(string $name): bool
    {
        return Iter\contains_key($this->helpers, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function exists(TemplateReferenceInterface|string $name): bool
    {
        try {
            $this->load($name);
        } catch (InvalidArgumentException) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(TemplateReferenceInterface|string $name): bool
    {
        if (!$name instanceof TemplateReferenceInterface) {
            $template = $this->parser->parse($name);
        } else {
            $template = $name;
        }

        return 'php' === $template->getEngineName();
    }

    /**
     * Gets a helper value.
     *
     * @param string $offset The helper name
     *
     * @throws InvalidArgumentException if the helper is not defined
     */
    #[ReturnTypeWillChange]
    public function offsetGet($offset): HelperInterface
    {
        return $this->get($offset);
    }

    /**
     * Returns true if the helper is defined.
     *
     * @param string $offset The helper name
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Sets a helper.
     *
     * @param string $offset An alias
     * @param HelperInterface $value The helper instance
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($value, $offset);
    }

    /**
     * @throws LogicException
     */
    public function offsetUnset(mixed $offset): void
    {
        throw new LogicException(sprintf('You can\'t unset a helper (%s).', $offset));
    }

    /**
     * Sets the helpers.
     *
     * @param array<int|string, HelperInterface> $helpers An array of helper
     */
    public function setHelpers(array $helpers): void
    {
        $this->helpers = [];
        $this->addHelpers($helpers);
    }

    /**
     * Decorates the current template with another one.
     */
    public function extend(string $template): void
    {
        $this->parents[$this->current] = $template;
    }

    /**
     * Escapes a string by using the current charset.
     */
    public function escape(string $value, string $context = 'html'): string
    {
        if (is_numeric($value)) {
            return $value;
        }

        // If we deal with a scalar value, we can cache the result to increase
        // the performance when the same value is escaped multiple times (e.g. loops)
        // TODO(azjezz): remove this, it will cause memory issues in long-running server process.
        if (!isset(self::$escaperCache[$context][$value])) {
            self::$escaperCache[$context][$value] = $this->getEscaper($context)($value);
        }

        return self::$escaperCache[$context][$value];
    }

    /**
     * Gets an escaper for a given context.
     *
     * @throws InvalidArgumentException
     */
    public function getEscaper(string $context): callable
    {
        if (!isset($this->escapers[$context])) {
            throw new InvalidArgumentException(sprintf('No registered escaper for context "%s".', $context));
        }

        return $this->escapers[$context];
    }

    public function addGlobal(string $name, mixed $value): void
    {
        $this->globals[$name] = $value;
    }

    /**
     * Gets the loader associated with this engine.
     */
    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }
}
