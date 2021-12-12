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

namespace Hype\Storage;

/**
 * FileStorage represents a template stored on the filesystem.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class FileStorage extends Storage
{
    /**
     * Returns the content of the template.
     */
    public function getContent(): string
    {
        return file_get_contents($this->template);
    }
}
