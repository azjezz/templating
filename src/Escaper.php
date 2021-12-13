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

namespace Hype;

use Psl;

final class Escaper
{
    private string $charset = 'UTF-8';

    /**
     * @param list<Escaper\EscaperInterface> $escapers
     */
    public function __construct(
        private array $escapers,
        string $charset = 'UTF-8',
    ) {
        $this->setCharset($charset);
    }

    public static function create(string $charset = 'UTF-8'): self
    {
        return new self([
            new Escaper\HtmlEscaper($charset),
            new Escaper\JavaScriptEscaper($charset),
        ], $charset);
    }

    public function addEscaper(Escaper\EscaperInterface $escaper): void
    {
        $this->escapers[] = $escaper;
    }

    public function escape(string $content, string $context = 'html'): string
    {
        return $this->getEscaper($context)->escape($content);
    }

    public function getEscaper(string $context): Escaper\EscaperInterface
    {
        foreach ($this->escapers as $escaper) {
            if ($context === $escaper->getContext()) {
                return $escaper;
            }
        }

        Psl\invariant_violation('No escaper is configured for the "%s" context.', $context);
    }

    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
        foreach ($this->escapers as $escaper) {
            $escaper->setCharset($charset);
        }
    }

    public function getCharset(): string
    {
        return $this->charset;
    }
}
