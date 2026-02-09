<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Brand\UseCases;

use App\Domain\Brand\Actions\BuildBrandQueryAction;

class ListBrandsUseCase
{
    public function __construct(
        private BuildBrandQueryAction $buildQuery
    ) {}

    public function run(array $filters, int $perPage)
    {
        return $this->buildQuery
            ->run($filters)
            ->paginate($perPage);
    }
}
