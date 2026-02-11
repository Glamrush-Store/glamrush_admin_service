<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Vendor;

use App\Domain\Vendor\UseCases\UpdateVendorUseCase;
use App\Http\Controllers\Controller;
use App\Http\Requests\Vendor\UpdateVendorRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Vendor;

class UpdateVendorController extends Controller
{
    public function __construct(
        private UpdateVendorUseCase $useCase,
    ) {}

    public function __invoke(
        UpdateVendorRequest $request,
        Vendor $vendor
    ) {

        try {
            // Validation will later move to a FormRequest
            $result = $this->useCase->run($vendor, $request->validated());

            return ApiResponse::success($result, 'Vendor updated successfully', 201);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 500);
        }
    }
}
