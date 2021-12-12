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

namespace Hype\Tests\Fixtures;

use Hype\Helper\Helper;

final class SimpleHelper extends Helper
{
    protected string $value = '';

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->value;
    }

    public function getName(): string
    {
        return 'foo';
    }
}
