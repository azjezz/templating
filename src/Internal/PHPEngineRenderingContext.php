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

namespace Hype\Internal;

use Hype\Escaper;
use Hype\Helper\HelperInterface;
use Hype\PHPEngine;
use Hype\Storage\FileStorage;
use Hype\Storage\Storage;
use Hype\Storage\StringStorage;
use Hype\TemplateReferenceInterface;
use Psl;
use Psl\Iter;
use RuntimeException;

/**
 * @internal
 */
final class PHPEngineRenderingContext
{
    private ?string $parent = null;

    /**
     * @param array<string, HelperInterface> $helpers
     * @param array<string, mixed> $parametersa
     * @param list<string> $stack
     */
    public function __construct(
        private PHPEngine $engine,
        private Escaper $escaper,
        private array   $helpers = [],
        private readonly array   $parameters = [],
        public readonly array $stack = [],
    ) {
    }

    public function render(TemplateReferenceInterface|string $name, array $parameters = []): string
    {
        return $this->engine->render($name, $parameters);
    }

    public function extend(?string $template): void
    {
        $this->parent = $template;
    }

    /**
     * Escapes a string by using the current charset.
     */
    public function escape(string $value, string $context = 'html'): string
    {
        if (is_numeric($value)) {
            return $value;
        }

        return $this->escaper->escape($value, $context);
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

    public function evaluate(Storage $storage): string
    {
        Psl\invariant(!Iter\contains_key($this->parameters, 'this'), 'Invalid parameter (this).');
        Psl\invariant(!Iter\contains_key($this->parameters, 'view'), 'Invalid parameter (view).');

        $__template_storage__ = $storage;
        unset($storage);
        // the view variable is exposed to the required file below
        $view = $this;
        if ($__template_storage__ instanceof FileStorage) {
            $__template_parameters__ = $this->parameters;
            extract($__template_parameters__, EXTR_SKIP);
            unset($__template_parameters__);

            ob_start();
            require $__template_storage__;

            return ob_get_clean();
        }

        if ($__template_storage__ instanceof StringStorage) {
            $__template_parameters__ = $this->parameters;
            extract($__template_parameters__, EXTR_SKIP);
            unset($__template_parameters__);

            ob_start();
            eval('; ?>' . $__template_storage__ . '<?php ;');

            return ob_get_clean();
        }

        throw new RuntimeException(sprintf('The template "%s" cannot be rendered.', $__template_storage__->__toString()));
    }

    public function getParent(): ?string
    {
        return $this->parent;
    }
}
