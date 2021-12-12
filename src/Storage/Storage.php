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

namespace Hype\Storage;

/**
 * Storage is the base class for all storage classes.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Storage
{
    /**
     * @param string $template The template name
     */
    public function __construct(
        protected readonly string $template
    ) {
    }

    /**
     * Returns the object string representation.
     */
    public function __toString(): string
    {
        return $this->template;
    }

    /**
     * Returns the content of the template.
     */
    abstract public function getContent(): string;
}
