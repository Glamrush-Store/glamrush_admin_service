<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Vendor\UseCases;

class ListVendorsUseCase
{
    public function __construct(
        private ListVendorsAction $action
    ) {}

    public function run(array $filters = [], int $perPage = 15)
    {
        return $this->action->execute($filters, $perPage);
    }
}
