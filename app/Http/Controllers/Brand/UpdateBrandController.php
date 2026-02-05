<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brand\UpdateBrandRequest;
use App\Http\Responses\ApiResponse;
use App\Models\Brand;
use App\UseCases\Brand\UpdateBrandUseCase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UpdateBrandController extends Controller
{
    public function __invoke(
        Brand $brand,
        UpdateBrandRequest $request,
        UpdateBrandUseCase $useCase
    ): JsonResponse {
        try {
            $brand = $useCase->run(
                brand: $brand,
                data: $request->validated(),
                photo: $request->file('image')
            );

            return ApiResponse::success($brand, 'OK', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }

    }
}
