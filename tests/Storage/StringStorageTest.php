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

namespace Hype\Tests\Storage;

use Hype\Storage\Storage;
use Hype\Storage\StringStorage;
use PHPUnit\Framework\TestCase;

final class StringStorageTest extends TestCase
{
    public function testGetContent(): void
    {
        $storage = new StringStorage('foo');
        static::assertInstanceOf(Storage::class, $storage, 'StringStorage is an instance of Storage');
        $storage = new StringStorage('foo');
        static::assertEquals('foo', $storage->getContent(), '->getContent() returns the content of the template');
    }
}
