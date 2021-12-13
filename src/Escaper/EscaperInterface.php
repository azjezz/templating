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

namespace Hype\Escaper;

interface EscaperInterface
{
    public function setCharset(string $charset): void;

    public function getCharset(): string;

    public function escape(string $content): string;

    public function getContext(): string;
}
