<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Vendor\UseCases;

use App\Models\Vendor;

class DeleteVendorUseCase
{
    public function __construct(
        private DeleteVendorAction $action
    ) {}

    public function run(Vendor $vendor): void
    {
        $this->action->execute($vendor);
    }
}
