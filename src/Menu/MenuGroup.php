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
    public function __construct(
        public string $key,
        public string $label,
        public int $priority = 50,
    ) {
        if (trim($this->key) === '') {
            throw new InvalidArgumentException('Menu group key cannot be empty.');
        }

        if (trim($this->label) === '') {
            throw new InvalidArgumentException('Menu group label cannot be empty.');
        }
    }

    /**
     * Create a menu group from an array payload.
     *
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $key = (string) ($data['key'] ?? '');

        return new self(
            key: $key,
            label: (string) ($data['label'] ?? self::labelFromKey($key)),
            priority: (int) ($data['priority'] ?? 50),
        );
    }

    /**
     * Create a human-readable label from a group key.
     */
    public static function labelFromKey(string $key): string
    {
        return ucwords(str_replace(['-', '_'], ' ', $key));
    }

    /**
     * Convert the group to an array for rendering.
     *
     * @return array{key: string, label: string, priority: int}
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
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
