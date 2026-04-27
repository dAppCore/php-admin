<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search\Providers;

use Core\Admin\Search\SearchProvider;
use Core\Admin\Search\SearchResult;
use Core\Tenant\Models\Workspace;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class WorkspaceSearchProvider implements SearchProvider
{
    /**
     * @param  class-string<Model>  $modelClass
     *
     * @example
     * $provider = new WorkspaceSearchProvider(Workspace::class, 10);
     */
    public function __construct(
        private readonly string $modelClass = Workspace::class,
        private readonly int $limit = 10
    ) {}

    /**
     * @return array<int, SearchResult>
     *
     * @example
     * $results = $provider->search('primary-site');
     */
    public function search(string $query): array
    {
        $query = trim($query);

        if ($query === '' || ! is_a($this->modelClass, Model::class, true)) {
            return [];
        }

        $term = $this->likeTerm($query);
        $modelClass = $this->modelClass;

        return $modelClass::query()
            ->where(function (Builder $builder) use ($term): void {
                $builder->whereRaw("name LIKE ? ESCAPE '!'", [$term])
                    ->orWhereRaw("slug LIKE ? ESCAPE '!'", [$term]);
            })
            ->get()
            ->map(fn (Model $workspace): SearchResult => $this->resultFor($workspace, $query))
            ->sortByDesc(static fn (SearchResult $result): int => $result->score)
            ->take($this->limit)
            ->values()
            ->all();
    }

    /**
     * Get the display label for workspace results.
     *
     * @example
     * $provider->getLabel(); // "Workspaces"
     */
    public function getLabel(): string
    {
        return 'Workspaces';
    }

    /**
     * Get the provider priority used by the dispatcher.
     *
     * @example
     * $provider->getPriority(); // 90
     */
    public function getPriority(): int
    {
        return 90;
    }

    /**
     * Convert a workspace model into a scored search result.
     *
     * @example
     * $result = $this->resultFor($workspace, 'primary');
     */
    private function resultFor(Model $workspace, string $query): SearchResult
    {
        $name = (string) ($workspace->getAttribute('name') ?? '');
        $slug = (string) ($workspace->getAttribute('slug') ?? '');
        $title = $name !== '' ? $name : $slug;

        return new SearchResult(
            title: $title,
            subtitle: $slug,
            url: '/hub/workspaces/'.$slug,
            icon: 'fa-folder',
            category: $this->getLabel(),
            score: $this->score($query, $name, $slug),
        );
    }

    /**
     * Escape wildcard characters and wrap a query for portable SQL LIKE.
     *
     * @example
     * $term = $this->likeTerm('docs_');
     */
    private function likeTerm(string $query): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query).'%';
    }

    /**
     * Score a result by exact, prefix, and substring relevance.
     *
     * @example
     * $score = $this->score('primary', 'Primary Site', 'primary-site');
     */
    private function score(string $query, string $name, string $slug): int
    {
        $query = strtolower($query);
        $name = strtolower($name);
        $slug = strtolower($slug);

        return match (true) {
            $name === $query => 100,
            str_starts_with($name, $query) => 90,
            $slug === $query => 85,
            str_contains($name, $query) => 80,
            str_starts_with($slug, $query) => 75,
            str_contains($slug, $query) => 70,
            default => 50,
        };
    }
}
