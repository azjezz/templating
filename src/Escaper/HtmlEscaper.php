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

use Psl\Html;

final class HtmlEscaper implements EscaperInterface
{
    public const CONTEXT  = 'html';

    public function __construct(
        private string $charset
    ) {
    }

    public function escape(string $content): string
    {
        // Numbers and Boolean values get turned into strings which can cause problems
        // with type comparisons (e.g. === or is_int() etc).
        return Html\encode_special_characters($content, false, Html\Encoding::from($this->getCharset()));
    }

    public function getContext(): string
    {
        return self::CONTEXT;
    }

    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }
}
