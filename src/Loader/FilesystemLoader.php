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

namespace Hype\Loader;

use Hype\Storage\FileStorage;
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
    protected $templatePathPatterns;

    /**
     * @param string|string[] $templatePathPatterns An array of path patterns to look for templates
     */
    public function __construct($templatePathPatterns)
    {
        $this->templatePathPatterns = (array)$templatePathPatterns;
    }

    /**
     * {@inheritDoc}
     */
    public function isFresh(TemplateReferenceInterface $template, int $time)
    {
        if (false === $storage = $this->load($template)) {
            return false;
        }

        return filemtime((string)$storage) < $time;
    }

    /**
     * {@inheritDoc}
     */
    public function load(TemplateReferenceInterface $template)
    {
        $file = $template->get('name');

        if (self::isAbsolutePath($file) && is_file($file)) {
            return new FileStorage($file);
        }

        $replacements = [];
        foreach ($template->all() as $key => $value) {
            $replacement_key = '%' . $key . '%';
            $replacements[$replacement_key] = $value;
        }

        $fileFailures = [];
        foreach ($this->templatePathPatterns as $templatePathPattern) {
            if (is_file($file = strtr($templatePathPattern, $replacements)) && is_readable($file)) {
                if (null !== $this->logger) {
                    $this->logger->debug('Loaded template file.', ['file' => $file]);
                }

                return new FileStorage($file);
            }

            if (null !== $this->logger) {
                $fileFailures[] = $file;
            }
        }

        // only log failures if no template could be loaded at all
        foreach ($fileFailures as $file) {
            if (null !== $this->logger) {
                $this->logger->debug('Failed loading template file.', ['file' => $file]);
            }
        }

        return false;
    }

    /**
     * Returns true if the file is an existing absolute path.
     *
     * @return bool
     */
    protected static function isAbsolutePath(string $file)
    {
        if ('/' === $file[0] || '\\' === $file[0]) {
            return true;
        }

        if (strlen($file) > 3 && ctype_alpha($file[0]) && ':' === $file[1] && ('\\' === $file[2] || '/' === $file[2])) {
            return true;
        }

        return null !== parse_url($file, PHP_URL_SCHEME);
    }
}