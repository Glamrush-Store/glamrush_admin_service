<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\UseCases;

use App\Domain\Product\Actions\BuildProductQueryAction;

class ListProductsUseCase
{
    public function __construct(
        private BuildProductQueryAction $buildQuery
    ) {}

    public function run(array $filters, int $perPage)
    {
        return $this->buildQuery
            ->run($filters)
            ->paginate($perPage);
    }
}
