<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hype\Tests\Loader;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Hype\Loader\Loader;
use Hype\TemplateReferenceInterface;

class LoaderTest extends TestCase
{
    public function testGetSetLogger()
    {
        $loader = new ProjectTemplateLoader4();
        $logger = $this->createMock(LoggerInterface::class);
        $loader->setLogger($logger);
        $this->assertSame($logger, $loader->getLogger(), '->setLogger() sets the logger instance');
    }
}

class ProjectTemplateLoader4 extends Loader
{
    public function load(TemplateReferenceInterface $template)
    {
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function isFresh(TemplateReferenceInterface $template, int $time): bool
    {
        return false;
    }
}
