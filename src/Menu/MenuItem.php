<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Menu;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Immutable admin menu item definition.
 */
final readonly class MenuItem implements Arrayable, JsonSerializable
{
    public function __construct(
        public string $label,
        public string|Closure $route,
        public string $icon,
        public string $group,
        public int $priority = 50,
    ) {
        if (trim($this->label) === '') {
            throw new InvalidArgumentException('Menu item label cannot be empty.');
        }

        if (is_string($this->route) && trim($this->route) === '') {
            throw new InvalidArgumentException('Menu item route cannot be empty.');
        }

        if (trim($this->icon) === '') {
            throw new InvalidArgumentException('Menu item icon cannot be empty.');
        }

        if (trim($this->group) === '') {
            throw new InvalidArgumentException('Menu item group cannot be empty.');
        }
    }

    /**
     * Create a menu item from an array payload.
     *
     * This accepts both the DTO shape and the RFC's lazy `item` wrapper shape
     * to keep module registration concise.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $payload = $data;

        if (array_key_exists('item', $data)) {
            $item = $data['item'];
            $payload = $item instanceof Closure ? $item() : $item;

            if (! is_array($payload)) {
                throw new InvalidArgumentException('Lazy menu item definitions must resolve to an array.');
            }

            $payload = array_merge(
                array_diff_key($data, ['item' => true]),
                $payload
            );
        }

        $route = $payload['route'] ?? $payload['href'] ?? null;

        if (! is_string($route) && ! $route instanceof Closure) {
            throw new InvalidArgumentException('Menu item route must be a string or closure.');
        }

        return new self(
            label: (string) ($payload['label'] ?? ''),
            route: $route,
            icon: (string) ($payload['icon'] ?? ''),
            group: (string) ($payload['group'] ?? ''),
            priority: (int) ($payload['priority'] ?? 50),
        );
    }

    /**
     * Resolve the item route when the item is rendered.
     */
    public function resolveRoute(): string
    {
        $route = $this->route instanceof Closure ? ($this->route)() : $this->route;

        if (! is_string($route) || trim($route) === '') {
            throw new InvalidArgumentException('Menu item route must resolve to a non-empty string.');
        }

        return $route;
    }

    /**
     * Convert the item to an array for rendering.
     *
     * @return array{label: string, route: string, icon: string, group: string, priority: int}
     */
    public function toArray(): array
    {
        return [
            'label' => $this->label,
            'route' => $this->resolveRoute(),
            'icon' => $this->icon,
            'group' => $this->group,
            'priority' => $this->priority,
        ];
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array{label: string, route: string, icon: string, group: string, priority: int}
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
