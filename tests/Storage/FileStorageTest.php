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

use Hype\Storage\FileStorage;
use Hype\Storage\Storage;
use PHPUnit\Framework\TestCase;

class FileStorageTest extends TestCase
{
    public function testGetContent()
    {
        $storage = new FileStorage('foo');
        static::assertInstanceOf(Storage::class, $storage, 'FileStorage is an instance of Storage');
        $storage = new FileStorage(__DIR__ . '/../Fixtures/templates/foo.php');
        static::assertEquals('<?php

declare(strict_types=1);

echo $foo;' . "\n", $storage->getContent(), '->getContent() returns the content of the template');
    }
}
