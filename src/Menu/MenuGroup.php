<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Menu;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;
use JsonSerializable;

/**
 * Immutable admin menu group definition.
 */
final readonly class MenuGroup implements Arrayable, JsonSerializable
{
    public string $key;

    public string $label;

    public int $priority;

    /**
     * Create an immutable menu group with normalised keys and labels.
     *
     * @example
     * $group = new MenuGroup('reports', 'Reports', 25);
     */
    public function __construct(string $key, string $label, int $priority = 50)
    {
        $key = trim($key);
        $label = trim($label);

        if ($key === '') {
            throw new InvalidArgumentException('Menu group key cannot be empty.');
        }

        if ($label === '') {
            throw new InvalidArgumentException('Menu group label cannot be empty.');
        }

        $this->key = $key;
        $this->label = $label;
        $this->priority = $priority;
    }

    /**
     * Create a menu group from an array payload.
     *
     * @param  array<string, mixed>  $data
     *
     * @example
     * MenuGroup::fromArray(['key' => 'reports', 'label' => 'Reports']);
     */
    public static function fromArray(array $data): self
    {
        $key = trim((string) ($data['key'] ?? ''));
        $label = trim((string) ($data['label'] ?? self::labelFromKey($key)));

        return new self(
            key: $key,
            label: $label,
            priority: (int) ($data['priority'] ?? 50),
        );
    }

    /**
     * Create a human-readable label from a group key.
     *
     * @example
     * MenuGroup::labelFromKey('custom-tools'); // "Custom Tools"
     */
    public static function labelFromKey(string $key): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $key));
    }

    /**
     * Convert the group to an array for rendering.
     *
     * @return array{key: string, label: string, priority: int}
     *
     * @example
     * $group->toArray();
     */
    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'priority' => $this->priority,
        ];
    }

    /**
     * Specify data which should be serialized to JSON.
     *
     * @return array{key: string, label: string, priority: int}
     *
     * @example
     * json_encode($group);
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
