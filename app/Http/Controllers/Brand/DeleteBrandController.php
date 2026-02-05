<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Brand;
use App\UseCases\Brand\DeleteBrandUseCase;
use Illuminate\Http\JsonResponse;

class DeleteBrandController extends Controller
{
    public function __invoke(
        Brand $brand,
        DeleteBrandUseCase $useCase
    ): JsonResponse {
        try {
            $useCase->run($brand);

            return ApiResponse::success([], 'OK', 204);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }
}
