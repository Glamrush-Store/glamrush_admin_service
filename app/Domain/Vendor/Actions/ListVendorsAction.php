<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Vendor\Actions;

use App\Domain\Vendor\Queries\VendorQueryBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListVendorsAction
{
    public function __construct(
        private VendorQueryBuilder $queryBuilder
    ) {}

    public function execute(
        array $filters = [],
        int $perPage = 15
    ): LengthAwarePaginator {
        return $this->queryBuilder
            ->apply($filters)
            ->orderByLatest()
            ->getQuery()
            ->paginate($perPage);
    }
}
