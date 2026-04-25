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
use Core\Tenant\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserSearchProvider implements SearchProvider
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function __construct(
        private readonly string $modelClass = User::class,
        private readonly int $limit = 10
    ) {}

    /**
     * @return array<int, SearchResult>
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
                $builder->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term);
            })
            ->limit($this->limit)
            ->get()
            ->map(fn (Model $user): SearchResult => $this->resultFor($user, $query))
            ->all();
    }

    public function getLabel(): string
    {
        return 'Users';
    }

    public function getPriority(): int
    {
        return 100;
    }

    private function resultFor(Model $user, string $query): SearchResult
    {
        $name = (string) ($user->getAttribute('name') ?? '');
        $email = (string) ($user->getAttribute('email') ?? '');
        $title = $name !== '' ? $name : $email;

        return new SearchResult(
            title: $title,
            subtitle: $email !== '' ? $email : null,
            url: '/hub/platform/user/'.$user->getKey(),
            icon: 'fa-user',
            category: $this->getLabel(),
            score: $this->score($query, $name, $email),
        );
    }

    private function likeTerm(string $query): string
    {
        return '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query).'%';
    }

    private function score(string $query, string $name, string $email): int
    {
        $query = strtolower($query);
        $name = strtolower($name);
        $email = strtolower($email);

        return match (true) {
            $name === $query => 100,
            str_starts_with($name, $query) => 90,
            $email === $query => 85,
            str_contains($name, $query) => 80,
            str_starts_with($email, $query) => 75,
            str_contains($email, $query) => 70,
            default => 50,
        };
    }
}
