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

namespace Hype\Tests\Helper;

use Hype\Helper\Helper;
use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    public function testGetSetCharset()
    {
        $helper = new ProjectTemplateHelper();
        $helper->setCharset('ISO-8859-1');
        static::assertSame('ISO-8859-1', $helper->getCharset(), '->setCharset() sets the charset set related to this helper');
    }
}

class ProjectTemplateHelper extends Helper
{
    public function getName(): string
    {
        return 'foo';
    }
}
