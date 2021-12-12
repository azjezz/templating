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

namespace Hype\Tests\Loader;

use Hype\Loader\CacheLoader;
use Hype\Loader\Loader;
use Hype\Loader\LoaderInterface;
use Hype\Storage\Storage;
use Hype\Storage\StringStorage;
use Hype\TemplateReference;
use Hype\TemplateReferenceInterface;
use PHPUnit\Framework\TestCase;
use Psl\Class;
use Psl\Env;
use Psl\Filesystem;
use Psl\PseudoRandom;
use Psl\Str;
use Psr\Log\LoggerInterface;

final class CacheLoaderTest extends TestCase
{
    public function testConstructor(): void
    {
        $loader = new ProjectTemplateLoader($varLoader = new ProjectTemplateLoaderVar(), sys_get_temp_dir());
        static::assertSame($loader->getLoader(), $varLoader, '__construct() takes a template loader as its first argument');
        static::assertEquals(sys_get_temp_dir(), $loader->getDir(), '__construct() takes a directory where to store the cache as its second argument');
    }

    public function testLoad(): void
    {
        $dir = Env\temp_dir() . Filesystem\SEPARATOR . PseudoRandom\int(111111, 999999);
        Filesystem\create_directory($dir);

        $loader = new ProjectTemplateLoader(new ProjectTemplateLoaderVar(), $dir);
        static::assertNull($loader->load(new TemplateReference('foo', 'php')), '->load() returns false if the embed loader is not able to load the template');

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('debug')
            ->with('Storing template in cache.', ['name' => 'index']);

        $loader->setLogger($logger);
        $loader->load(new TemplateReference('index'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('debug')
            ->with('Fetching template from cache.', ['name' => 'index']);

        $loader->setLogger($logger);
        $loader->load(new TemplateReference('index'));
    }
}

class ProjectTemplateLoader extends CacheLoader
{
    public function getDir(): string
    {
        return $this->directory;
    }

    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }
}

class ProjectTemplateLoaderVar extends Loader
{
    public function getIndexTemplate(): string
    {
        return 'Hello World';
    }

    public function getSpecialTemplate(): string
    {
        return 'Hello {{ name }}';
    }

    public function load(TemplateReferenceInterface $template): ?Storage
    {
        if (Class\has_method($this::class, $method = 'get' . Str\Byte\capitalize($template->getLogicalName()) . 'Template')) {
            return new StringStorage($this->$method());
        }

        return null;
    }

    public function isFresh(TemplateReferenceInterface $template, int $time): bool
    {
        return false;
    }
}
