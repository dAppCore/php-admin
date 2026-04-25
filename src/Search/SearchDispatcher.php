<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search;

class SearchDispatcher
{
    /**
     * @var array<int, SearchProvider>
     */
    private array $providers = [];

    /**
     * @param  iterable<SearchProvider>  $providers
     */
    public function __construct(iterable $providers = [])
    {
        foreach ($providers as $provider) {
            $this->register($provider);
        }
    }

    public function register(SearchProvider $provider): self
    {
        $this->providers[] = $provider;

        return $this;
    }

    /**
     * @return array<int, SearchProvider>
     */
    public function providers(): array
    {
        return $this->providers;
    }

    /**
     * Gather results from all providers and rank by score descending.
     *
     * @return array<int, SearchResult>
     */
    public function search(string $query): array
    {
        $query = trim($query);

        if ($query === '') {
            return [];
        }

        $ranked = [];
        $index = 0;

        foreach ($this->providers as $provider) {
            foreach ($provider->search($query) as $result) {
                if (! $result instanceof SearchResult) {
                    continue;
                }

                $ranked[] = [
                    'result' => $result,
                    'priority' => $provider->getPriority(),
                    'index' => $index++,
                ];
            }
        }

        usort($ranked, static function (array $left, array $right): int {
            $score = $right['result']->score <=> $left['result']->score;

            if ($score !== 0) {
                return $score;
            }

            $priority = $right['priority'] <=> $left['priority'];

            if ($priority !== 0) {
                return $priority;
            }

            return $left['index'] <=> $right['index'];
        });

        return array_map(
            static fn (array $entry): SearchResult => $entry['result'],
            $ranked
        );
    }
}
