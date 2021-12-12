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

use InvalidArgumentException;

/**
 * Interface to be implemented by all templates.
 *
 * @author Victor Berchet <victor@suumit.com>
 */
interface TemplateReferenceInterface
{
    /**
     * Gets the template parameters.
     *
     * @return array
     */
    public function all();

    /**
     * Sets a template parameter.
     *
     * @throws InvalidArgumentException if the parameter name is not supported
     *
     * @return $this
     */
    public function set(string $name, string $value);

    /**
     * Gets a template parameter.
     *
     * @throws InvalidArgumentException if the parameter name is not supported
     *
     * @return string
     */
    public function get(string $name);

    /**
     * Returns the path to the template.
     *
     * By default, it just returns the template name.
     *
     * @return string
     */
    public function getPath();

    /**
     * Returns the "logical" template name.
     *
     * The template name acts as a unique identifier for the template.
     *
     * @return string
     */
    public function getLogicalName();

    /**
     * Returns the string representation as shortcut for getLogicalName().
     *
     * Alias of getLogicalName().
     *
     * @return string
     */
    public function __toString();
}