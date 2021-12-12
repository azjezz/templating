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

use Hype\TemplateNameParser;
use Hype\TemplateReference;
use PHPUnit\Framework\TestCase;

class TemplateNameParserTest extends TestCase
{
    protected $parser;

    protected function setUp(): void
    {
        $this->parser = new TemplateNameParser();
    }

    protected function tearDown(): void
    {
        $this->parser = null;
    }

    /**
     * @dataProvider getLogicalNameToTemplateProvider
     */
    public function testParse($name, $ref)
    {
        $template = $this->parser->parse($name);

        static::assertEquals($template->getLogicalName(), $ref->getLogicalName());
        static::assertEquals($template->getLogicalName(), $name);
    }

    public function getLogicalNameToTemplateProvider()
    {
        return [
            ['/path/to/section/name.engine', new TemplateReference('/path/to/section/name.engine', 'engine')],
            ['name.engine', new TemplateReference('name.engine', 'engine')],
            ['name', new TemplateReference('name')],
        ];
    }
}
