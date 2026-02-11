<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Vendor\UseCases;

use App\Domain\Vendor\Actions\UpdateVendorAction;
use App\Models\Vendor;

class UpdateVendorUseCase
{
    public function __construct(
        private UpdateVendorAction $action
    ) {}

    public function run(Vendor $vendor, array $data): Vendor
    {
        return $this->action->execute($vendor, $data);
    }
}
