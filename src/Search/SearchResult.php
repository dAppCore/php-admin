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

    /**
     * Create a search result from named arguments or supported positional shapes.
     *
     * @example
     * $result = new SearchResult(title: 'Dashboard', url: '/hub', category: 'Pages', score: 90);
     */
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
     *
     * @param  array<string, mixed>  $data
     *
     * @example
     * SearchResult::fromArray(['title' => 'Dashboard', 'url' => '/hub']);
     */
    public static function fromArray(array $data): static
    {
        $args = [];

        foreach (['id', 'title', 'url', 'type', 'icon', 'subtitle', 'meta', 'category', 'score'] as $key) {
            if (array_key_exists($key, $data)) {
                $args[$key] = $data[$key];
            }
        }

        return new self(...$args);
    }

    /**
     * Create a SearchResult with a new type and icon.
     *
     * Used by the registry to set type/icon from the provider.
     *
     * @example
     * $result = $result->withTypeAndIcon('pages', 'rectangle-stack');
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
     *
     * @return array{id: string, title: string, subtitle: ?string, url: string, type: string, icon: string, meta: array}
     *
     * @example
     * $payload = $result->toArray();
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
     *
     * @return array{id: string, title: string, subtitle: ?string, url: string, type: string, icon: string, meta: array}
     *
     * @example
     * json_encode($result);
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    /**
     * Normalise both the legacy registry constructor and the new DTO shape.
     *
     * @param  array<int|string, mixed>  $arguments
     * @return array<string, mixed>
     *
     * @example
     * self::normaliseConstructorArguments(['Dashboard', null, '/hub', 'fa-house', 'Pages', 90]);
     */
    private static function normaliseConstructorArguments(array $arguments): array
    {
        if (! array_is_list($arguments)) {
            return $arguments;
        }

        if (count($arguments) >= 6 && is_numeric($arguments[5]) && self::looksLikeCategory($arguments[4] ?? null)) {
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

    /**
     * Detect whether a positional argument looks like a result category.
     *
     * @example
     * self::looksLikeCategory('Workspaces'); // true
     */
    private static function looksLikeCategory(mixed $value): bool
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return false;
        }

        $category = strtolower(trim((string) $value));

        return $category !== ''
            && ! str_starts_with($category, 'fa-')
            && ! str_contains($category, ' fa-')
            && ! str_contains($category, 'fa ');
    }
}
