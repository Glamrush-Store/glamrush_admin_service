<?php

/*
 * (c) 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\UseCases;

use App\Domain\Product\Actions\SyncProductCategoriesAction;
use App\Domain\Product\Actions\SyncSimpleProductVariantAction;
use App\Domain\Product\Actions\UpdateProductAction;
use App\Domain\Product\Actions\UploadProductPhotosAction;
use App\Domain\Product\Events\ProductSavedEvent;
use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class UpdateProductUseCase
{
    public function __construct(
        private UpdateProductAction $updateProduct,
        private SyncSimpleProductVariantAction $syncSimpleVariant,
        private UploadProductPhotosAction $uploadProductPhotos,
        private CreateAppLogAction $log,
        private SyncProductCategoriesAction $syncCategories,
    ) {}

    public function execute(Product $product, array $data): Product
    {
        try {
            return DB::transaction(function () use ($product, $data) {
                $this->updateProduct->run($product, $data);
                $this->syncSimpleVariant->run($product->refresh());

                if (array_key_exists('category_ids', $data) || array_key_exists('category_id', $data)) {
                    $this->syncCategories->run(
                        $product,
                        $data['category_ids'] ?? [$data['category_id']],
                        $data['primary_category_id'] ?? $data['category_id'] ?? null,
                        $data['category_sequences'] ?? []
                    );
                }

                if (! empty($data['photos'])) {
                    $this->uploadProductPhotos->run($product, $data['photos']);
                }

                event(new ProductSavedEvent($product));

                return $product->load(['variants', 'categories', 'primaryCategory']);
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
