<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Vendor;

use App\Domain\Vendor\UseCases\ShowVendorUseCase;
use App\Http\Controllers\Controller;
use App\Http\Resources\Vendor\VendorResource;
use App\Http\Responses\ApiResponse;
use App\Models\Vendor;

class ShowVendorController extends Controller
{
    public function __construct(
        private ShowVendorUseCase $useCase
    ) {}

    public function __invoke(Vendor $vendor)
    {
        $vendor = $this->useCase->run($vendor);

        return ApiResponse::success(new VendorResource($vendor));
    }
}
