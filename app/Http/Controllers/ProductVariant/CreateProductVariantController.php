<?php

namespace App\Http\Controllers\ProductVariant;

use App\Domain\Product\ProductVariant\UseCases\CreateProductVariantUseCase;
use App\Http\Requests\ProductVariant\CreateProductVariantRequest;
use App\Http\Resources\ProductVariant\ProductVariantResource;
use App\Http\Responses\ApiResponse;
use App\Models\Product;

class CreateProductVariantController
{
    public function __construct(private CreateProductVariantUseCase $useCase) {}

    public function __invoke(
        CreateProductVariantRequest $request,
        Product $product,
    ) {
        $variant = $this->useCase->execute($product, $request->validated());

        return ApiResponse::success(
            new ProductVariantResource($variant),
            'Product variant created',
            201,
        );
    }
}
