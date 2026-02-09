<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */


namespace App\Http\Controllers\Product;

use App\Domain\Product\UseCases\DeleteProductUseCase;
use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class DeleteProductController extends Controller
{
    public function __invoke(
        Product $product,
        DeleteProductUseCase $useCase
    ): JsonResponse {
        try {
            $useCase->run($product);
            return ApiResponse::success([], 'OK', 204);
        } catch (\Throwable $e) {
            return ApiResponse::error($e->getMessage(), [], 400);
        }
    }
}
