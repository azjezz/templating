<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hype\Tests\Loader;

use Hype\Loader\FilesystemLoader;
use Hype\Storage\FileStorage;
use Hype\TemplateReference;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class FilesystemLoaderTest extends TestCase
{
    protected static $fixturesPath;

    public static function setUpBeforeClass(): void
    {
        self::$fixturesPath = realpath(__DIR__ . '/../Fixtures/');
    }

    public function testConstructor()
    {
        $pathPattern = self::$fixturesPath . '/templates/%name%.%engine%';
        $loader = new ProjectTemplateLoader2($pathPattern);
        static::assertEquals([$pathPattern], $loader->getTemplatePathPatterns(), '__construct() takes a path as its second argument');
        $loader = new ProjectTemplateLoader2([$pathPattern]);
        static::assertEquals([$pathPattern], $loader->getTemplatePathPatterns(), '__construct() takes an array of paths as its second argument');
    }

    public function testIsAbsolutePath()
    {
        static::assertTrue(ProjectTemplateLoader2::isAbsolutePath('/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        static::assertTrue(ProjectTemplateLoader2::isAbsolutePath('c:\\\\foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        static::assertTrue(ProjectTemplateLoader2::isAbsolutePath('c:/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        static::assertTrue(ProjectTemplateLoader2::isAbsolutePath('\\server\\foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        static::assertTrue(ProjectTemplateLoader2::isAbsolutePath('https://server/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
        static::assertTrue(ProjectTemplateLoader2::isAbsolutePath('phar://server/foo.xml'), '->isAbsolutePath() returns true if the path is an absolute path');
    }

    public function testLoad()
    {
        $pathPattern = self::$fixturesPath . '/templates/%name%';
        $path = self::$fixturesPath . '/templates';
        $loader = new ProjectTemplateLoader2($pathPattern);
        $storage = $loader->load(new TemplateReference($path . '/foo.php', 'php'));
        static::assertInstanceOf(FileStorage::class, $storage, '->load() returns a FileStorage if you pass an absolute path');
        static::assertEquals($path . '/foo.php', (string) $storage, '->load() returns a FileStorage pointing to the passed absolute path');

        static::assertFalse($loader->load(new TemplateReference('bar', 'php')), '->load() returns false if the template is not found');

        $storage = $loader->load(new TemplateReference('foo.php', 'php'));
        static::assertInstanceOf(FileStorage::class, $storage, '->load() returns a FileStorage if you pass a relative template that exists');
        static::assertEquals($path . '/foo.php', (string) $storage, '->load() returns a FileStorage pointing to the absolute path of the template');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(static::exactly(2))->method('debug');

        $loader = new ProjectTemplateLoader2($pathPattern);
        $loader->setLogger($logger);
        static::assertFalse($loader->load(new TemplateReference('foo.xml', 'php')), '->load() returns false if the template does not exist for the given engine');

        $loader = new ProjectTemplateLoader2([self::$fixturesPath . '/null/%name%', $pathPattern]);
        $loader->setLogger($logger);
        $loader->load(new TemplateReference('foo.php', 'php'));
    }
}

class ProjectTemplateLoader2 extends FilesystemLoader
{
    public function getTemplatePathPatterns()
    {
        return $this->templatePathPatterns;
    }

    public static function isAbsolutePath($path): bool
    {
        return parent::isAbsolutePath($path);
    }
}
