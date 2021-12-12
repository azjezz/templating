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

use Hype\Storage\Storage;
use Hype\TemplateReferenceInterface;

/**
 * ChainLoader is a loader that calls other loaders to load templates.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ChainLoader extends Loader
{
    protected array $loaders = [];

    /**
     * @param LoaderInterface[] $loaders
     */
    public function __construct(array $loaders = [])
    {
        foreach ($loaders as $loader) {
            $this->addLoader($loader);
        }
    }

    public function addLoader(LoaderInterface $loader): void
    {
        $this->loaders[] = $loader;
    }

    /**
     * {@inheritDoc}
     */
    public function load(TemplateReferenceInterface $template): ?Storage
    {
        foreach ($this->loaders as $loader) {
            if (null !== $storage = $loader->load($template)) {
                return $storage;
            }
        }

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function isFresh(TemplateReferenceInterface $template, int $time): bool
    {
        foreach ($this->loaders as $loader) {
            return $loader->isFresh($template, $time);
        }

        return false;
    }
}
