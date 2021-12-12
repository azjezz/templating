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

namespace Hype\Tests\Helper;

use Exception;
use Hype\Helper\SlotsHelper;
use PHPUnit\Framework\TestCase;
use Psl\Exception\InvariantViolationException;

final class SlotsHelperTest extends TestCase
{
    public function testHasGetSet(): void
    {
        $helper = new SlotsHelper();
        $helper->set('foo', 'bar');
        static::assertEquals('bar', $helper->get('foo'), '->set() sets a slot value');
        static::assertEquals('bar', $helper->get('bar', 'bar'), '->get() takes a default value to return if the slot does not exist');

        static::assertTrue($helper->has('foo'), '->has() returns true if the slot exists');
        static::assertFalse($helper->has('bar'), '->has() returns false if the slot does not exist');
    }

    public function testOutput(): void
    {
        $helper = new SlotsHelper();
        $helper->set('foo', 'bar');
        ob_start();
        $ret = $helper->output('foo');
        $output = ob_get_clean();
        static::assertEquals('bar', $output, '->output() outputs the content of a slot');
        static::assertTrue($ret, '->output() returns true if the slot exists');

        ob_start();
        $ret = $helper->output('bar', 'bar');
        $output = ob_get_clean();
        static::assertEquals('bar', $output, '->output() takes a default value to return if the slot does not exist');
        static::assertTrue($ret, '->output() returns true if the slot does not exist but a default value is provided');

        ob_start();
        $ret = $helper->output('bar');
        $output = ob_get_clean();
        static::assertEquals('', $output, '->output() outputs nothing if the slot does not exist');
        static::assertFalse($ret, '->output() returns false if the slot does not exist');
    }

    public function testStartStop(): void
    {
        $helper = new SlotsHelper();
        $helper->start('bar');
        echo 'foo';
        $helper->stop();
        static::assertEquals('foo', $helper->get('bar'), '->start() starts a slot');
        static::assertTrue($helper->has('bar'), '->starts() starts a slot');

        $helper->start('bar');
        try {
            $helper->start('bar');
            $helper->stop();
            static::fail('->start() throws an InvalidArgumentException if a slot with the same name is already started');
        } catch (Exception $e) {
            $helper->stop();
            static::assertInstanceOf(InvariantViolationException::class, $e, '->start() throws an InvalidArgumentException if a slot with the same name is already started');
            static::assertEquals('A slot named "bar" is already started.', $e->getMessage(), '->start() throws an InvalidArgumentException if a slot with the same name is already started');
        }

        try {
            $helper->stop();
            static::fail('->stop() throws an LogicException if no slot is started');
        } catch (Exception $e) {
            static::assertInstanceOf(InvariantViolationException::class, $e, '->stop() throws an LogicException if no slot is started');
            static::assertEquals('No slot started.', $e->getMessage(), '->stop() throws an LogicException if no slot is started');
        }
    }
}
