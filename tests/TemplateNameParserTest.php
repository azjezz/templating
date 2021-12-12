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
    protected TemplateNameParser $parser;

    protected function setUp(): void
    {
        $this->parser = new TemplateNameParser();
    }

    /**
     * @dataProvider getLogicalNameToTemplateProvider
     */
    public function testParse(string $name, TemplateReference $ref): void
    {
        $template = $this->parser->parse($name);

        static::assertEquals($template->getLogicalName(), $ref->getLogicalName());
        static::assertEquals($template->getLogicalName(), $name);
    }

    /**
     * @return list<array{0: string, 1: TemplateReference}>
     */
    public function getLogicalNameToTemplateProvider(): array
    {
        return [
            ['/path/to/section/name.engine', new TemplateReference('/path/to/section/name.engine', 'engine')],
            ['name.engine', new TemplateReference('name.engine', 'engine')],
            ['name', new TemplateReference('name')],
        ];
    }
}
