<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search\Providers;

use Core\Admin\Search\Concerns\BuildsLikeTerms;
use Core\Admin\Search\SearchProvider;
use Core\Admin\Search\SearchResult;
use Core\Tenant\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class UserSearchProvider implements SearchProvider
{
    use BuildsLikeTerms;

    /**
     * @param  class-string<Model>  $modelClass
     * @param  int  $candidateLimit  Database-level safety cap before in-memory ranking.
     *
     * @example
     * $provider = new UserSearchProvider(User::class, 10, 1000);
     */
    public function __construct(
        private readonly string $modelClass = User::class,
        private readonly int $limit = 10,
        private readonly int $candidateLimit = 1000
    ) {}

    /**
     * @return array<int, SearchResult>
     *
     * @example
     * $results = $provider->search('alice@example.test');
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
                    ->orWhereRaw("email LIKE ? ESCAPE '!'", [$term]);
            })
            ->limit($this->candidateLimit())
            ->get()
            ->map(fn (Model $user): SearchResult => $this->resultFor($user, $query))
            ->sortByDesc(static fn (SearchResult $result): int => $result->score)
            ->take($this->limit)
            ->values()
            ->all();
    }

    /**
     * Get the display label for user results.
     *
     * @example
     * $provider->getLabel(); // "Users"
     */
    public function getLabel(): string
    {
        return 'Users';
    }

    /**
     * Get the provider priority used by the dispatcher.
     *
     * @example
     * $provider->getPriority(); // 100
     */
    public function getPriority(): int
    {
        return 100;
    }

    /**
     * Convert a user model into a scored search result.
     *
     * @example
     * $result = $this->resultFor($user, 'alice');
     */
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

    /**
     * Get the bounded candidate count loaded before in-memory ranking.
     *
     * @example
     * $limit = $this->candidateLimit();
     */
    private function candidateLimit(): int
    {
        return max($this->limit, $this->candidateLimit, 1);
    }

    /**
     * Score a result by exact, prefix, and substring relevance.
     *
     * @example
     * $score = $this->score('alice', 'Alice Admin', 'alice@example.test');
     */
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
