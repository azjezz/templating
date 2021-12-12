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

namespace Hype\Helper;

/**
 * Helper is the base class for all helper classes.
 *
 * Most of the time, a Helper is an adapter around an existing
 * class that exposes a read-only interface for templates.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
abstract class Helper implements HelperInterface
{
    protected string $charset = 'UTF-8';

    /**
     * Sets the default charset.
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    /**
     * Gets the default charset.
     */
    public function getCharset(): string
    {
        return $this->charset;
    }
}
