<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search;

use Illuminate\Contracts\Support\Arrayable;
use JsonSerializable;

final class SearchResult implements Arrayable, JsonSerializable
{
    public readonly string $id;

    public readonly string $title;

    public readonly ?string $subtitle;

    public readonly string $url;

    public readonly string $icon;

    public readonly string $category;

    public readonly int $score;

    public readonly string $type;

    public readonly array $meta;

    public function __construct(mixed ...$arguments)
    {
        $data = self::normaliseConstructorArguments($arguments);

        $this->id = (string) ($data['id'] ?? uniqid('', true));
        $this->title = (string) ($data['title'] ?? '');
        $this->subtitle = isset($data['subtitle']) ? (string) $data['subtitle'] : null;
        $this->url = (string) ($data['url'] ?? '#');
        $this->type = (string) ($data['type'] ?? $data['category'] ?? 'unknown');
        $this->icon = (string) ($data['icon'] ?? 'document');
        $this->category = (string) ($data['category'] ?? $this->type);
        $this->score = (int) ($data['score'] ?? 0);
        $this->meta = is_array($data['meta'] ?? null) ? $data['meta'] : [];
    }

    /**
     * Create a SearchResult from an array.
     */
    public static function fromArray(array $data): static
    {
        return new self(
            id: (string) ($data['id'] ?? uniqid('', true)),
            title: (string) ($data['title'] ?? ''),
            url: (string) ($data['url'] ?? '#'),
            type: (string) ($data['type'] ?? $data['category'] ?? 'unknown'),
            icon: (string) ($data['icon'] ?? 'document'),
            subtitle: $data['subtitle'] ?? null,
            meta: $data['meta'] ?? [],
            category: (string) ($data['category'] ?? $data['type'] ?? 'unknown'),
            score: (int) ($data['score'] ?? 0),
        );
    }

    /**
     * Create a SearchResult with a new type and icon.
     *
     * Used by the registry to set type/icon from the provider.
     */
    public function withTypeAndIcon(string $type, string $icon): static
    {
        return new static(
            id: $this->id,
            title: $this->title,
            url: $this->url,
            type: $type,
            icon: $this->icon !== 'document' ? $this->icon : $icon,
            subtitle: $this->subtitle,
            meta: $this->meta,
            category: $type,
            score: $this->score,
        );
    }

    /**
     * Convert the result to an array.
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'url' => $this->url,
            'type' => $this->type,
            'icon' => $this->icon,
            'meta' => $this->meta,
        ];
    }

    /**
     * Specify data which should be serialized to JSON.
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Normalise both the legacy registry constructor and the new DTO shape.
     */
    private static function normaliseConstructorArguments(array $arguments): array
    {
        if (! array_is_list($arguments)) {
            return $arguments;
        }

        if (count($arguments) >= 6 && is_numeric($arguments[5])) {
            return [
                'title' => $arguments[0] ?? '',
                'subtitle' => $arguments[1] ?? null,
                'url' => $arguments[2] ?? '#',
                'icon' => $arguments[3] ?? 'document',
                'category' => $arguments[4] ?? 'unknown',
                'type' => $arguments[4] ?? 'unknown',
                'score' => $arguments[5] ?? 0,
            ];
        }

        return [
            'id' => $arguments[0] ?? uniqid('', true),
            'title' => $arguments[1] ?? '',
            'url' => $arguments[2] ?? '#',
            'type' => $arguments[3] ?? 'unknown',
            'icon' => $arguments[4] ?? 'document',
            'subtitle' => $arguments[5] ?? null,
            'meta' => $arguments[6] ?? [],
            'category' => $arguments[3] ?? 'unknown',
        ];
    }
}
