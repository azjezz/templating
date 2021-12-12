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

namespace Hype\Tests;

use Hype\DelegatingEngine;
use Hype\EngineInterface;
use Hype\StreamingEngineInterface;
use LogicException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class DelegatingEngineTest extends TestCase
{
    public function testRenderDelegatesToSupportedEngine()
    {
        $firstEngine = $this->getEngineMock('template.php', false);
        $secondEngine = $this->getEngineMock('template.php', true);

        $secondEngine->expects(static::once())
            ->method('render')
            ->with('template.php', ['foo' => 'bar'])
            ->willReturn('<html />');

        $delegatingEngine = new DelegatingEngine([$firstEngine, $secondEngine]);
        $result = $delegatingEngine->render('template.php', ['foo' => 'bar']);

        static::assertSame('<html />', $result);
    }

    public function testRenderWithNoSupportedEngine()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No engine is able to work with the template "template.php"');
        $firstEngine = $this->getEngineMock('template.php', false);
        $secondEngine = $this->getEngineMock('template.php', false);

        $delegatingEngine = new DelegatingEngine([$firstEngine, $secondEngine]);
        $delegatingEngine->render('template.php', ['foo' => 'bar']);
    }

    public function testStreamDelegatesToSupportedEngine()
    {
        $streamingEngine = $this->getStreamingEngineMock('template.php', true);
        $streamingEngine->expects(static::once())
            ->method('stream')
            ->with('template.php', ['foo' => 'bar'])
            ->willReturn('<html />');

        $delegatingEngine = new DelegatingEngine([$streamingEngine]);
        $result = $delegatingEngine->stream('template.php', ['foo' => 'bar']);

        static::assertNull($result);
    }

    public function testStreamRequiresStreamingEngine()
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Template "template.php" cannot be streamed as the engine supporting it does not implement StreamingEngineInterface');
        $delegatingEngine = new DelegatingEngine([new TestEngine()]);
        $delegatingEngine->stream('template.php', ['foo' => 'bar']);
    }

    public function testExists()
    {
        $engine = $this->getEngineMock('template.php', true);
        $engine->expects(static::once())
            ->method('exists')
            ->with('template.php')
            ->willReturn(true);

        $delegatingEngine = new DelegatingEngine([$engine]);

        static::assertTrue($delegatingEngine->exists('template.php'));
    }

    public function testSupports()
    {
        $engine = $this->getEngineMock('template.php', true);

        $delegatingEngine = new DelegatingEngine([$engine]);

        static::assertTrue($delegatingEngine->supports('template.php'));
    }

    public function testSupportsWithNoSupportedEngine()
    {
        $engine = $this->getEngineMock('template.php', false);

        $delegatingEngine = new DelegatingEngine([$engine]);

        static::assertFalse($delegatingEngine->supports('template.php'));
    }

    public function testGetExistingEngine()
    {
        $firstEngine = $this->getEngineMock('template.php', false);
        $secondEngine = $this->getEngineMock('template.php', true);

        $delegatingEngine = new DelegatingEngine([$firstEngine, $secondEngine]);

        static::assertSame($secondEngine, $delegatingEngine->getEngine('template.php'));
    }

    public function testGetInvalidEngine()
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No engine is able to work with the template "template.php"');
        $firstEngine = $this->getEngineMock('template.php', false);
        $secondEngine = $this->getEngineMock('template.php', false);

        $delegatingEngine = new DelegatingEngine([$firstEngine, $secondEngine]);
        $delegatingEngine->getEngine('template.php');
    }

    private function getEngineMock($template, $supports)
    {
        $engine = $this->createMock(EngineInterface::class);

        $engine->expects(static::once())
            ->method('supports')
            ->with($template)
            ->willReturn($supports);

        return $engine;
    }

    private function getStreamingEngineMock($template, $supports)
    {
        $engine = $this->getMockForAbstractClass(MyStreamingEngine::class);

        $engine->expects(static::once())
            ->method('supports')
            ->with($template)
            ->willReturn($supports);

        return $engine;
    }
}

interface MyStreamingEngine extends EngineInterface, StreamingEngineInterface
{
}

class TestEngine implements EngineInterface
{
    public function render($name, array $parameters = []): string
    {
    }

    public function exists($name): bool
    {
    }

    public function supports($name): bool
    {
        return true;
    }

    public function stream()
    {
    }
}
