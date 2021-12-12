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

namespace Hype\Loader;

use Hype\Storage\Storage;
use Hype\TemplateReferenceInterface;

/**
 * LoaderInterface is the interface all loaders must implement.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface LoaderInterface
{
    /**
     * Loads a template.
     */
    public function load(TemplateReferenceInterface $template): ?Storage;

    /**
     * Returns true if the template is still fresh.
     *
     * @param int $time The last modification time of the cached template (timestamp)
     */
    public function isFresh(TemplateReferenceInterface $template, int $time): bool;
}
