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

namespace Hype\Tests;

use Exception;
use Hype\Helper\SlotsHelper;
use Hype\Loader\Loader;
use Hype\Loader\LoaderInterface;
use Hype\PhpEngine;
use Hype\Storage\Storage;
use Hype\Storage\StringStorage;
use Hype\TemplateNameParser;
use Hype\TemplateReference;
use Hype\TemplateReferenceInterface;
use Hype\Tests\Fixtures\SimpleHelper;
use InvalidArgumentException;
use LogicException;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;

class PhpEngineTest extends TestCase
{
    protected ProjectTemplateLoader $loader;

    protected function setUp(): void
    {
        $this->loader = new ProjectTemplateLoader();
    }

    public function testConstructor(): void
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        static::assertEquals($this->loader, $engine->getLoader(), '__construct() takes a loader instance as its second first argument');
    }

    public function testOffsetGet(): void
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $engine->set($helper = new SimpleHelper('bar'), 'foo');
        static::assertEquals($helper, $engine['foo'], '->offsetGet() returns the value of a helper');

        try {
            $engine['bar'];
            static::fail('->offsetGet() throws an InvalidArgumentException if the helper is not defined');
        } catch (Exception $e) {
            static::assertInstanceOf(InvariantViolationException::class, $e, '->offsetGet() throws an InvariantViolationException if the helper is not defined');
            static::assertEquals('The helper "bar" is not defined.', $e->getMessage(), '->offsetGet() throws an InvariantViolationException if the helper is not defined');
        }
    }

    public function testGetSetHas(): void
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $foo = new SimpleHelper('foo');
        $engine->set($foo);
        static::assertEquals($foo, $engine->get('foo'), '->set() sets a helper');

        $engine['bar'] = $foo;
        static::assertEquals($foo, $engine->get('bar'), '->set() takes an alias as a second argument');

        static::assertArrayHasKey('bar', $engine);

        try {
            $engine->get('foobar');
            static::fail('->get() throws an InvalidArgumentException if the helper is not defined');
        } catch (Exception $e) {
            static::assertInstanceOf(InvariantViolationException::class, $e, '->get() throws an InvariantViolationException if the helper is not defined');
            static::assertEquals('The helper "foobar" is not defined.', $e->getMessage(), '->get() throws an InvariantViolationException if the helper is not defined');
        }

        static::assertArrayHasKey('bar', $engine);
        static::assertTrue($engine->has('foo'), '->has() returns true if the helper exists');
        static::assertFalse($engine->has('foobar'), '->has() returns false if the helper does not exist');
    }

    public function testUnsetHelper(): void
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $foo = new SimpleHelper('foo');
        $engine->set($foo);

        $this->expectException(LogicException::class);

        unset($engine['foo']);
    }

    public function testExtendRender(): void
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader, []);
        try {
            $engine->render('name');
            static::fail('->render() throws an InvalidArgumentException if the template does not exist');
        } catch (Exception $e) {
            static::assertInstanceOf(InvalidArgumentException::class, $e, '->render() throws an InvalidArgumentException if the template does not exist');
            static::assertEquals('The template "name" does not exist.', $e->getMessage(), '->render() throws an InvalidArgumentException if the template does not exist');
        }

        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader, [new SlotsHelper()]);
        $engine->set(new SimpleHelper('bar'));
        $this->loader->setTemplate('foo.php', '<?php $this->extend("layout.php"); echo $this[\'foo\'].$foo ?>');
        $this->loader->setTemplate('layout.php', '-<?php echo $this[\'slots\']->get("_content") ?>-');
        static::assertEquals('-barfoo-', $engine->render('foo.php', ['foo' => 'foo']), '->render() uses the decorator to decorate the template');

        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader, [new SlotsHelper()]);
        $engine->set(new SimpleHelper('bar'));
        $this->loader->setTemplate('bar.php', 'bar');
        $this->loader->setTemplate('foo.php', '<?php $this->extend("layout.php"); echo $foo ?>');
        $this->loader->setTemplate('layout.php', '<?php echo $this->render("bar.php") ?>-<?php echo $this[\'slots\']->get("_content") ?>-');
        static::assertEquals('bar-foo-', $engine->render('foo.php', ['foo' => 'foo', 'bar' => 'bar']), '->render() supports render() calls in templates');
    }

    public function testRenderParameter(): void
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $this->loader->setTemplate('foo.php', '<?php echo $template . $parameters ?>');
        static::assertEquals('foobar', $engine->render('foo.php', ['template' => 'foo', 'parameters' => 'bar']), '->render() extract variables');
    }

    /**
     * @dataProvider forbiddenParameterNames
     */
    public function testRenderForbiddenParameter($name): void
    {
        $this->expectException(InvalidArgumentException::class);
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $this->loader->setTemplate('foo.php', 'bar');
        $engine->render('foo.php', [$name => 'foo']);
    }

    public function forbiddenParameterNames(): array
    {
        return [
            ['this'],
            ['view'],
        ];
    }

    public function testEscape(): void
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        static::assertEquals('&lt;br /&gt;', $engine->escape('<br />'), '->escape() escapes strings');
    }

    public function testGetSetCharset(): void
    {
        $helper = new SlotsHelper();
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader, [$helper]);
        static::assertEquals('UTF-8', $engine->getCharset(), 'EngineInterface::getCharset() returns UTF-8 by default');
        static::assertEquals('UTF-8', $helper->getCharset(), 'HelperInterface::getCharset() returns UTF-8 by default');

        $engine->setCharset('ISO-8859-1');
        static::assertEquals('ISO-8859-1', $engine->getCharset(), 'EngineInterface::setCharset() changes the default charset to use');
        static::assertEquals('ISO-8859-1', $helper->getCharset(), 'EngineInterface::setCharset() changes the default charset of helper');
    }

    public function testGlobalVariables(): void
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $engine->addGlobal('global_variable', 'lorem ipsum');

        static::assertEquals([
            'global_variable' => 'lorem ipsum',
        ], $engine->getGlobals());
    }

    public function testGlobalsGetPassedToTemplate(): void
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);
        $engine->addGlobal('global', 'global variable');

        $this->loader->setTemplate('global.php', '<?php echo $global; ?>');

        static::assertEquals('global variable', $engine->render('global.php'));

        static::assertEquals('overwritten', $engine->render('global.php', ['global' => 'overwritten']));
    }

    public function testGetLoader(): void
    {
        $engine = new ProjectTemplateEngine(new TemplateNameParser(), $this->loader);

        static::assertSame($this->loader, $engine->getLoader());
    }
}

final class ProjectTemplateEngine extends PhpEngine
{
    public function getLoader(): LoaderInterface
    {
        return $this->loader;
    }
}

final class ProjectTemplateLoader extends Loader
{
    /**
     * @var array<string, TemplateReference>
     */
    public array $templates = [];

    public function setTemplate(string $name, string $content): void
    {
        $template = new TemplateReference($name, 'php');
        $this->templates[$template->getLogicalName()] = $content;
    }

    public function load(TemplateReferenceInterface $template): ?Storage
    {
        if (isset($this->templates[$template->getLogicalName()])) {
            return new StringStorage($this->templates[$template->getLogicalName()]);
        }

        return null;
    }

    public function isFresh(TemplateReferenceInterface $template, int $time): bool
    {
        return false;
    }
}
