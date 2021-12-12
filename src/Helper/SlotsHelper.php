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

namespace Hype\Helper;

use Psl;
use Psl\Iter;

/**
 * SlotsHelper manages template slots.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SlotsHelper extends Helper
{
    protected array $slots = [];
    protected array $openSlots = [];

    /**
     * Starts a new slot.
     *
     * This method starts an output buffer that will be
     * closed when the stop() method is called.
     *
     * @throws Psl\Exception\InvariantViolationException if a slot with the same name is already started
     */
    public function start(string $name): void
    {
        Psl\invariant(!Iter\contains($this->openSlots, $name), 'A slot named "%s" is already started.', $name);

        $this->openSlots[] = $name;
        $this->slots[$name] = '';

        ob_start();
        ob_implicit_flush(PHP_VERSION_ID >= 80000 ? false : 0);
    }

    /**
     * Stops a slot.
     *
     * @throws Psl\Exception\InvariantViolationException if no slot has been started
     */
    public function stop(): void
    {
        Psl\invariant(!Iter\is_empty($this->openSlots), 'No slot started.');

        $name = array_pop($this->openSlots);

        $this->slots[$name] = ob_get_clean();
    }

    /**
     * Returns true if the slot exists.
     */
    public function has(string $name): bool
    {
        return Iter\contains_key($this->slots, $name);
    }

    /**
     * Gets the slot value.
     *
     * @param string|null $default The default slot content
     */
    public function get(string $name, ?string $default = null): ?string
    {
        return $this->slots[$name] ?? $default;
    }

    /**
     * Sets a slot value.
     */
    public function set(string $name, string $content): void
    {
        $this->slots[$name] = $content;
    }

    /**
     * Outputs a slot.
     *
     * @param string|null $default The default slot content
     *
     * @return bool true if the slot is defined or if a default content has been provided, false otherwise
     */
    public function output(string $name, ?string $default = null): bool
    {
        if (!$this->has($name)) {
            if (null !== $default) {
                echo $default;

                return true;
            }

            return false;
        }

        echo $this->slots[$name];

        return true;
    }

    /**
     * Returns the canonical name of this helper.
     */
    public function getName(): string
    {
        return 'slots';
    }
}
