<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Category\UseCases;

use App\Domain\Category\Actions\BuildCategoryQueryAction;

class ListCategoriesUseCase
{
    public function __construct(
        private BuildCategoryQueryAction $buildQuery
    ) {}

    public function run(array $filters, int $perPage)
    {
        return $this->buildQuery
            ->run($filters)
            ->paginate($perPage);
    }
}
