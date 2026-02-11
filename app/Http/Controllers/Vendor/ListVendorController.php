<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Vendor;

use App\Domain\Vendor\UseCases\ListVendorsUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\Request;

class ListVendorController extends Controller
{
    public function __construct(
        private ListVendorsUseCase $useCase
    ) {}

    public function __invoke(Request $request)
    {
        $vendors = $this->useCase->run(
            filters: $request->query(),
            perPage: (int) $request->query('per_page', 15)
        );

        return ApiResponse::success($vendors);
    }
}
