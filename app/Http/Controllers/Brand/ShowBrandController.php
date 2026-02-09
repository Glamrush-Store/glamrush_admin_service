<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Controllers\Brand;

use App\Domain\Brand\UseCases\ShowBrandUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Brand;

class ShowBrandController extends Controller
{
    public function __invoke(
        Brand $brand,
        ShowBrandUseCase $useCase
    ) {
        try {
            $result = $useCase->run($brand);

            return ApiResponse::success($result, 'OK', 200);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }

    }
}
