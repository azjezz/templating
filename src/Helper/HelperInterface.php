<?php

declare(strict_types=1);

/*
 * This file is part of the Hype.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hype\Helper;

/**
 * HelperInterface is the interface all helpers must implement.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface HelperInterface
{
    /**
     * Returns the canonical name of this helper.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the default charset.
     */
    public function setCharset(string $charset);

    /**
     * Gets the default charset.
     *
     * @return string
     */
    public function getCharset();
}
