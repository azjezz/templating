<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hype;

/**
 * Internal representation of a template.
 *
 * @author Victor Berchet <victor@suumit.com>
 * @author Saif Eddin Gmati <azjezz@protonmail.com>
 *
 * @psalm-immutable
 */
final class TemplateReference implements TemplateReferenceInterface
{
    public function __construct(
        private readonly string $name,
        private readonly ?string $engine = null
    ){
    }

    /**
     * {@inheritDoc}
     *
     * @mutation-free
     */
    public function __toString(): string
    {
        return $this->getLogicalName();
    }

    /**
     * {@inheritDoc}
     *
     * @mutation-free
     */
    public function getPath(): string
    {
        return $this->getLogicalName();
    }

    /**
     * {@inheritDoc}
     *
     * @mutation-free
     */
    public function getLogicalName(): string
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     *
     * @mutation-free
     */
    public function getEngineName(): ?string
    {
        return $this->engine;
    }
}
