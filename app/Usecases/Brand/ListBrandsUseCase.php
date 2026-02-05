<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Usecases\Brand;

use App\Actions\Brand\BuildBrandQueryAction;

class ListBrandsUseCase
{
    public function __construct(
        private BuildBrandQueryAction $buildQuery
    ) {
    }

    public function run(array $filters, int $perPage)
    {
        return $this->buildQuery
            ->run($filters)
            ->paginate($perPage);
    }
}
