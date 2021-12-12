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

/**
 * Interface to be implemented by all templates.
 *
 * @author Victor Berchet <victor@suumit.com>
 * @author Saif Eddin Gmati <azjezz@protonmail.com>
 */
interface TemplateReferenceInterface
{
    /**
     * Returns the path to the template.
     *
     * By default, it just returns the template name.
     */
    public function getPath(): string;

    /**
     * Returns the "logical" template name.
     *
     * The template name acts as a unique identifier for the template.
     */
    public function getLogicalName(): string;

    /**
     * Returns the "logical" engine name.
     *
     * The template name acts as a unique identifier for the engine used to render this template.
     */
    public function getEngineName(): ?string;

    /**
     * Returns the string representation as shortcut for getLogicalName().
     *
     * Alias of getLogicalName().
     */
    public function __toString(): string;
}
