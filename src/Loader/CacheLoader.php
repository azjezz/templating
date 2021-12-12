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

use Hype\Storage\FileStorage;
use Hype\Storage\Storage;
use Hype\TemplateReferenceInterface;
use RuntimeException;

use const DIRECTORY_SEPARATOR;

/**
 * CacheLoader is a loader that caches other loaders responses
 * on the filesystem.
 *
 * This cache only caches on disk to allow PHP accelerators to cache the opcodes.
 * All other mechanism would imply the use of `eval()`.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class CacheLoader extends Loader
{
    /**
     * @param string $directory The directory where to store the cache files
     */
    public function __construct(
        protected LoaderInterface $loader,
        protected string $directory
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function load(TemplateReferenceInterface $template): ?Storage
    {
        $key = hash('sha256', $template->getLogicalName());
        $dir = $this->directory . DIRECTORY_SEPARATOR . substr($key, 0, 2);
        $file = substr($key, 2) . '.tpl';
        $path = $dir . DIRECTORY_SEPARATOR . $file;

        if (is_file($path)) {
            $this->logger?->debug('Fetching template from cache.', ['name' => $template->getLogicalName()]);

            return new FileStorage($path);
        }

        if (null === $storage = $this->loader->load($template)) {
            return null;
        }

        $content = $storage->getContent();

        if (!is_dir($dir) && !@mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Cache Loader was not able to create directory "%s".', $dir));
        }

        file_put_contents($path, $content);

        $this->logger?->debug('Storing template in cache.', ['name' => $template->getLogicalName()]);

        return new FileStorage($path);
    }

    /**
     * {@inheritDoc}
     */
    public function isFresh(TemplateReferenceInterface $template, int $time): bool
    {
        return $this->loader->isFresh($template, $time);
    }
}
