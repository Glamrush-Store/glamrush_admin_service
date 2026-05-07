<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Vendor\UseCases;

use App\Domain\Vendor\Actions\CreateVendorAction;
use App\Models\Vendor;

class CreateVendorUseCase
{
    public function __construct(
        private CreateVendorAction $action
    ) {}

    public function run(array $data): Vendor
    {
        return $this->action->execute($data);
    }
}
