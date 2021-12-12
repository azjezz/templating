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

use LogicException;
use RuntimeException;

/**
 * DelegatingEngine selects an engine for a given template.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class DelegatingEngine implements EngineInterface, StreamingEngineInterface
{
    /**
     * @param list<EngineInterface> $engines A list of EngineInterface instances to add
     */
    public function __construct(
        protected array $engines = []
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function render(TemplateReferenceInterface|string $name, array $parameters = []): string
    {
        return $this->getEngine($name)->render($name, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function stream(TemplateReferenceInterface|string $name, array $parameters = []): void
    {
        $engine = $this->getEngine($name);
        if (!$engine instanceof StreamingEngineInterface) {
            throw new LogicException(sprintf('Template "%s" cannot be streamed as the engine supporting it does not implement StreamingEngineInterface.', $name));
        }

        $engine->stream($name, $parameters);
    }

    /**
     * {@inheritDoc}
     */
    public function exists(TemplateReferenceInterface|string $name): bool
    {
        return $this->getEngine($name)->exists($name);
    }

    public function addEngine(EngineInterface $engine): void
    {
        $this->engines[] = $engine;
    }

    /**
     * {@inheritDoc}
     */
    public function supports(TemplateReferenceInterface|string $name): bool
    {
        try {
            $this->getEngine($name);
        } catch (RuntimeException) {
            return false;
        }

        return true;
    }

    /**
     * Get an engine able to render the given template.
     *
     * @throws RuntimeException if no engine able to work with the template is found
     */
    public function getEngine(TemplateReferenceInterface|string $name): EngineInterface
    {
        foreach ($this->engines as $engine) {
            if ($engine->supports($name)) {
                return $engine;
            }
        }

        throw new RuntimeException(sprintf('No engine is able to work with the template "%s".', $name));
    }
}
