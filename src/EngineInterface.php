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

use RuntimeException;

/**
 * EngineInterface is the interface each engine must implement.
 *
 * All methods rely on a template name. A template name is a
 * "logical" name for the template, and as such it does not refer to
 * a path on the filesystem (in fact, the template can be stored
 * anywhere, like in a database).
 *
 * The methods should accept any name. If the name is not an instance of
 * TemplateReferenceInterface, a TemplateNameParserInterface should be used to
 * convert the name to a TemplateReferenceInterface instance.
 *
 * Each template loader uses the logical template name to look for
 * the template.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
interface EngineInterface
{
    /**
     * Renders a template.
     *
     * @param string|TemplateReferenceInterface $name A template name or a TemplateReferenceInterface instance
     * @param array $parameters An array of parameters to pass to the template
     *
     * @throws RuntimeException if the template cannot be rendered
     */
    public function render(TemplateReferenceInterface|string $name, array $parameters = []): string;

    /**
     * Returns true if the template exists.
     *
     * @throws RuntimeException if the engine cannot handle the template name
     */
    public function exists(TemplateReferenceInterface|string $name): bool;

    /**
     * Returns true if this class is able to render the given template.
     */
    public function supports(TemplateReferenceInterface|string $name): bool;
}
