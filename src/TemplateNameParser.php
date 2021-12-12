<?php

declare(strict_types=1);

/*
 * This file is part of the Symfony package.
 *
 * (c) Saif Eddin Gmati <azjezz@protonmail.com>
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Hype;

/**
 * TemplateNameParser is the default implementation of TemplateNameParserInterface.
 *
 * This implementation takes everything as the template name
 * and the extension for the engine.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TemplateNameParser implements TemplateNameParserInterface
{
    /**
     * {@inheritDoc}
     */
    public function parse(string $name): TemplateReferenceInterface
    {
        $engine = null;
        if (false !== $pos = strrpos($name, '.')) {
            $engine = substr($name, $pos + 1);
        }

        return new TemplateReference($name, $engine);
    }
}
