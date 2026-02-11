<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Vendor;

use App\Domain\Vendor\UseCases\CreateVendorUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\CreateVendorRequest;
use App\Http\Responses\ApiResponse;

class CreateVendorController extends Controller
{
    public function __construct(
        private CreateVendorUseCase $useCase
    ) {}

    public function __invoke(
        CreateVendorRequest $request,
    ) {
        $vendor = $this->useCase->run($request->all());

        return ApiResponse::success($vendor, 'Vendor created successfully', 201);
    }
}
