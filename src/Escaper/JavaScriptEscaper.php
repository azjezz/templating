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

use Psl\Regex;

use function bin2hex;
use function iconv;
use function substr;

final class JavaScriptEscaper implements EscaperInterface
{
    public const CONTEXT  = 'js';

    public function __construct(
        private string $charset
    ) {
    }

    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
    }

    public function getCharset(): string
    {
        return $this->charset;
    }

    public function escape(string $content): string
    {
        if ('UTF-8' !== $this->getCharset()) {
            $content = iconv($this->getCharset(), 'UTF-8', $content);
        }

        $callback = static function (array $matches): string {
            $char = $matches[0];

            // \xHH
            if (!isset($char[1])) {
                return '\\x' . substr('00' . bin2hex($char), -2);
            }

            // \uHHHH
            $char = iconv('UTF-8', 'UTF-16BE', $char);

            return '\\u' . substr('0000' . bin2hex($char), -4);
        };

        $content = Regex\replace_with($content, '#[^\p{L}\p{N} ]#u', $callback);

        if ('UTF-8' !== $this->getCharset()) {
            $content = iconv('UTF-8', $this->getCharset(), $content);
        }

        return $content;
    }

    public function getContext(): string
    {
        return self::CONTEXT;
    }
}
