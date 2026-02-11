<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\UseCases;

use App\Domain\Product\Actions\SyncProductVariantsAction;
use App\Domain\Product\Actions\UpdateProductAction;
use App\Domain\Product\Events\ProductSavedEvent;
use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateProductUseCase
{
    public function __construct(
        private UpdateProductAction $updateProduct,
        private SyncProductVariantsAction $syncVariants,
        private CreateAppLogAction $log
    ) {}

    public function execute(Product $product, array $data): Product
    {
        try {
            return DB::transaction(function () use ($product, $data) {
                $this->updateProduct->run($product, $data);

                // required if you want to change a product from simple to variant
                if ($product->type !== 'simple') {
                    $this->syncVariants->run($product, $data['variants']);

                    return $product->load('variants');
                }

                event(new ProductSavedEvent($product));

                return $product;
            });

        } catch (\Throwable $e) {
            $this->log->run(
                level: 'error',
                event: 'PRODUCT_UPDATE_FAILED',
                message: 'Failed to update product',
                context: [
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ],
                actor: auth()->user()
            );

            throw new \RuntimeException('failed to update Product', 0, $e);
        }

    }
}
