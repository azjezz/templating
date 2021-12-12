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

namespace Hype\Tests\Storage;

use Hype\Storage\Storage;
use PHPUnit\Framework\TestCase;

class StorageTest extends TestCase
{
    public function testMagicToString()
    {
        $storage = new TestStorage('foo');
        static::assertEquals('foo', (string) $storage, '__toString() returns the template name');
    }
}

class TestStorage extends Storage
{
    public function getContent(): string
    {
    }
}
