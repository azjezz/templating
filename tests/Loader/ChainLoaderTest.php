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

use Hype\Loader\ChainLoader;
use Hype\Loader\FilesystemLoader;
use Hype\Storage\FileStorage;
use Hype\TemplateReference;
use PHPUnit\Framework\TestCase;

final class ChainLoaderTest extends TestCase
{
    protected FilesystemLoader $loader1;
    protected FilesystemLoader $loader2;

    protected function setUp(): void
    {
        $fixturesPath = realpath(__DIR__ . '/../Fixtures/');
        $this->loader1 = new FilesystemLoader([$fixturesPath . '/null/%name%']);
        $this->loader2 = new FilesystemLoader([$fixturesPath . '/templates/%name%']);
    }

    public function testConstructor(): void
    {
        $loader = new ProjectTemplateLoader1([$this->loader1, $this->loader2]);
        static::assertEquals([$this->loader1, $this->loader2], $loader->getLoaders(), '__construct() takes an array of template loaders as its second argument');
    }

    public function testAddLoader(): void
    {
        $loader = new ProjectTemplateLoader1([$this->loader1]);
        $loader->addLoader($this->loader2);
        static::assertEquals([$this->loader1, $this->loader2], $loader->getLoaders(), '->addLoader() adds a template loader at the end of the loaders');
    }

    public function testLoad(): void
    {
        $loader = new ProjectTemplateLoader1([$this->loader1, $this->loader2]);
        static::assertNull($loader->load(new TemplateReference('bar', 'php')), '->load() returns null if the template is not found');
        static::assertNull($loader->load(new TemplateReference('foo', 'php')), '->load() returns null if the template does not exist for the given renderer');
        static::assertInstanceOf(
            FileStorage::class,
            $loader->load(new TemplateReference('foo.php', 'php')),
            '->load() returns a FileStorage if the template exists'
        );
    }
}

final class ProjectTemplateLoader1 extends ChainLoader
{
    public function getLoaders(): array
    {
        return $this->loaders;
    }
}
