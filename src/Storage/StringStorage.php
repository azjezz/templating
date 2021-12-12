<?php

/*
 * This file is part of the Symfony package.
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hype\Storage;

/**
 * StringStorage represents a template stored in a string.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class StringStorage extends Storage
{
    /**
     * Returns the content of the template.
     *
     * @return string
     */
    public function getContent()
    {
        return $this->template;
    }
}
