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

use function strlen;

use const PHP_URL_SCHEME;

/**
 * FilesystemLoader is a loader that read templates from the filesystem.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class FilesystemLoader extends Loader
{
    /**
     * @param list<string> $templatePathPatterns A list of path patterns to look for templates
     */
    public function __construct(
        protected array $templatePathPatterns
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function isFresh(TemplateReferenceInterface $template, int $time): bool
    {
        if (false === $storage = $this->load($template)) {
            return false;
        }

        return filemtime((string)$storage) < $time;
    }

    /**
     * {@inheritDoc}
     */
    public function load(TemplateReferenceInterface $template): ?Storage
    {
        $file = $template->getPath();

        if (self::isAbsolutePath($file) && is_file($file)) {
            return new FileStorage($file);
        }

        $replacements = [];
        foreach (['name' => $template->getLogicalName(), 'engine' => $template->getEngineName()] as $key => $value) {
            $replacement_key = '%' . $key . '%';
            $replacements[$replacement_key] = $value;
        }

        $fileFailures = [];
        foreach ($this->templatePathPatterns as $templatePathPattern) {
            if (is_file($file = strtr($templatePathPattern, $replacements)) && is_readable($file)) {
                $this->logger?->debug('Loaded template file.', ['file' => $file]);

                return new FileStorage($file);
            }

            if (null !== $this->logger) {
                $fileFailures[] = $file;
            }
        }

        // only log failures if no template could be loaded at all
        foreach ($fileFailures as $file) {
            $this->logger?->debug('Failed loading template file.', ['file' => $file]);
        }

        return null;
    }

    /**
     * Returns true if the file is an existing absolute path.
     */
    protected static function isAbsolutePath(string $file): bool
    {
        if ('/' === $file[0] || '\\' === $file[0]) {
            return true;
        }

        if (strlen($file) > 3 && ':' === $file[1] && ('\\' === $file[2] || '/' === $file[2]) && ctype_alpha($file[0])) {
            return true;
        }

        return null !== parse_url($file, PHP_URL_SCHEME);
    }
}
