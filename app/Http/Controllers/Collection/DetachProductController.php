<?php

namespace App\Http\Controllers\Collection;

use App\Domain\Collection\UseCases\DetachProductUseCase;
use App\Http\Responses\ApiResponse;
use App\Models\Collection;
use App\Models\Product;

class DetachProductController
{
    public function __construct(private DetachProductUseCase $useCase) {}

    public function __invoke(Collection $collection, Product $product)
    {
        $this->useCase->run($collection, $product);

        return ApiResponse::success(null, 'Product removed from collection');
    }
}
