<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Vendor;

use App\Domain\Vendor\UseCases\DeleteVendorUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;

class DeleteVendorController extends Controller
{
    public function __construct(
        private DeleteVendorUseCase $useCase
    ) {}

    public function __invoke($vendor)
    {
        $this->useCase->run($vendor);

        return ApiResponse::success('', 204);
    }
}
