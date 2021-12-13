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

use Hype\Helper\HelperInterface;
use Hype\Loader\LoaderInterface;
use Hype\Storage\Storage;
use InvalidArgumentException;
use Psl;
use Psl\Iter;
use Psl\Type;

/**
 * PhpEngine is an engine able to render PHP templates.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class PHPEngine implements EngineInterface
{
    protected string $charset = 'UTF-8';

    protected TemplateNameParserInterface $parser;
    protected LoaderInterface $loader;
    protected Escaper $escaper;

    /**
     * @var HelperInterface[]
     */
    protected array $helpers = [];

    /**
     * @var array<string, mixed>
     */
    protected array $globals = [];

    /**
     * @param HelperInterface[] $helpers An array of helper instances
     */
    public function __construct(TemplateNameParserInterface $parser, LoaderInterface $loader, array $helpers = [])
    {
        $this->parser = $parser;
        $this->loader = $loader;
        $this->escaper = Escaper::create();

        $this->addHelpers($helpers);
    }

    public function addEscaper(Escaper\EscaperInterface $escaper): void
    {
        $this->escaper->addEscaper($escaper);
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
     * {@inheritDoc}
     *
     * @throws InvalidArgumentException if the template does not exist
     */
    public function render(TemplateReferenceInterface|string $name, array $parameters = []): string
    {
        $parse = function (TemplateReferenceInterface|string $name): TemplateReferenceInterface {
            if ($name instanceof TemplateReferenceInterface) {
                $template = $name;
            } else {
                $template = $this->parser->parse($name);
            }

            return $template;
        };

        // attach the global variables
        $parameters = array_replace($this->getGlobals(), $parameters);

        $content = '';
        $parent = null;
        /**
         * @var list<string> $stack
         */
        $stack = [];
        $context = null;
        $helpers = $this->helpers;
        if (Iter\contains_key($helpers, 'slots')) {
            /** @var Helper\SlotsHelper $slots */
            $slots = clone $helpers['slots'];
            $helpers['slots'] = $slots;
        } else {
            $slots = null;
        }

        do {
            if ($parent !== null) {
                $stack = $context->stack;
                $stack[] = $slots->get('_content');
                $slots->set('_content', $content);
            }
            $template = $parse($name);
            $context = new Internal\PHPEngineRenderingContext($this, $this->escaper, $helpers, $parameters, $stack);
            // render
            $content = $context->evaluate($this->load($template));
            if (null !== $parent) {
                $slots->set('_content', array_pop($stack) ?: '');
            }

            $parent = $context->getParent();
            if (null !== $parent) {
                $name = $parent;
            }
        } while (null !== $parent);

        return $content;
    }

    /**
     * Loads the given template.
     *
     * @throws InvalidArgumentException if the template cannot be found
     */
    protected function load(TemplateReferenceInterface $template): Storage
    {
        $storage = $this->loader->load($template);

        if (null === $storage) {
            throw new InvalidArgumentException(sprintf('The template "%s" does not exist.', (string) $template));
        }

        return $storage;
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
     * Sets the helpers.
     *
     * @param array<int|string, HelperInterface> $helpers An array of helper
     */
    public function setHelpers(array $helpers): void
    {
        $this->helpers = [];
        $this->addHelpers($helpers);
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

    public function getEscaper(): Escaper
    {
        return $this->escaper;
    }
}
