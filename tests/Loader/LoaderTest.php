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

use Hype\Loader\Loader;
use Hype\Storage\Storage;
use Hype\TemplateReferenceInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class LoaderTest extends TestCase
{
    public function testGetSetLogger(): void
    {
        $loader = new ProjectTemplateLoader4();
        $logger = $this->createMock(LoggerInterface::class);
        $loader->setLogger($logger);
        static::assertSame($logger, $loader->getLogger(), '->setLogger() sets the logger instance');
    }
}

final class ProjectTemplateLoader4 extends Loader
{
    public function load(TemplateReferenceInterface $template): ?Storage
    {
        return null;
    }

    public function getLogger(): ?LoggerInterface
    {
        return $this->logger;
    }

    public function isFresh(TemplateReferenceInterface $template, int $time): bool
    {
        return false;
    }
}
