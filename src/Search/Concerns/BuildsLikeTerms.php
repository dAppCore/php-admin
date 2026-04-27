<?php

/*
 * Core PHP Framework
 *
 * Licensed under the European Union Public Licence (EUPL) v1.2.
 * See LICENSE file for details.
 */

declare(strict_types=1);

namespace Core\Admin\Search\Concerns;

trait BuildsLikeTerms
{
    /**
     * Escape wildcard characters and wrap a query for portable SQL LIKE.
     *
     * @example
     * $term = $this->likeTerm('100%');
     */
    protected function likeTerm(string $query): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $query).'%';
    }
}
