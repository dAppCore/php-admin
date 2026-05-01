<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search;

interface SearchProvider
{
    /**
     * Search for items matching the query.
     *
     * @return array<int, SearchResult>
     *
     * @example
     * return [new SearchResult(title: 'Dashboard', url: '/hub')];
     */
    public function search(string $query): array;

    /**
     * Get the provider label for grouping and display.
     *
     * @example
     * return 'Users';
     */
    public function getLabel(): string;

    /**
     * Get the provider priority for deterministic tie-breaking.
     *
     * @example
     * return 100;
     */
    public function getPriority(): int;
}
