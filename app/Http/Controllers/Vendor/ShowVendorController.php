<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Vendor;

use App\Domain\Vendor\UseCases\ShowVendorUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;

class ShowVendorController extends Controller
{
    public function __construct(
        private ShowVendorUseCase $useCase
    ) {}

    public function __invoke($vendor)
    {
        $vendor = $this->useCase->run($vendor);

        return ApiResponse::success($vendor);
    }
}
