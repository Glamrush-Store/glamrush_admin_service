<?php

/*
 * (c) 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\UseCases;

use App\Domain\Brand\Actions\GetBrandByIdAction;
use App\Domain\Product\Actions\CreateProductAction;
use App\Domain\Product\Actions\GenerateProductSkuAction;
use App\Domain\Product\Actions\SyncProductCategoriesAction;
use App\Domain\Product\Actions\SyncSimpleProductVariantAction;
use App\Domain\Product\Actions\UploadProductPhotosAction;
use App\Domain\Product\Events\ProductSavedEvent;
use App\Domain\Product\ProductVariant\Actions\CreateProductVariantsAction;
use App\Domain\Product\ProductVariant\Actions\GenerateVariantSkuAction;
use App\Domain\Product\ProductVariant\Actions\UploadVariantPhotosAction;
use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Domain\Shared\Actions\GenerateUniqueSlugAction;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class CreateProductUseCase
{
    public function __construct(
        private CreateProductAction $createProduct,
        private CreateProductVariantsAction $createVariants,
        private UploadVariantPhotosAction $uploadVariantPhotos,
        private GenerateProductSkuAction $generateSku,
        private CreateAppLogAction $log,
        private GetBrandByIdAction $getBrand,
        private GenerateVariantSkuAction $generateVariantSku,
        private GenerateUniqueSlugAction $generateSlug,
        private UploadProductPhotosAction $uploadProductPhotos,
        private SyncProductCategoriesAction $syncCategories,
        private SyncSimpleProductVariantAction $syncSimpleVariant,
    ) {}

    public function execute(array $data): Product
    {
        try {
            return DB::transaction(function () use ($data) {
                $sequence = (Product::max('sequence') ?? 0) + 1;
                $slug = $this->generateSlug->run($data['name']);

                $sku = $this->generateSku->run(
                    brandCode: $this->getBrand->run($data['brand_id'])->code,
                    productName: $data['name'],
                    sequence: $sequence
                );

                $product = $this->createProduct->run([
                    ...$data,
                    'sku' => $sku,
                    'sequence' => $sequence,
                    'slug' => $slug ?? $data['name'],
                ]);

                $this->syncCategories->run(
                    $product,
                    $data['category_ids'] ?? [$data['category_id']],
                    $data['primary_category_id'] ?? $data['category_id'] ?? null,
                    $data['category_sequences'] ?? []
                );

                if (! empty($data['photos'])) {
                    $this->uploadProductPhotos->run($product, $data['photos']);
                }

                if ($data['type'] === 'simple') {
                    $this->syncSimpleVariant->run($product);
                } elseif ($data['type'] === 'variable') {
                    foreach ($data['variants'] as $variantData) {
                        $variantData['sku'] = $this->generateVariantSku->run(
                            $product->sku,
                            $variantData['attributes'],
                        );

                        $variant = $this->createVariants->run($product, $variantData);

                        if (! empty($variantData['photos'])) {
                            $this->uploadVariantPhotos->run($variant, $variantData['photos']);
                        }
                    }
                }

                event(new ProductSavedEvent($product));

                return $product->load(['variants', 'categories', 'primaryCategory']);
            });
        } catch (\Throwable $e) {
            $this->log->run(
                level: 'error',
                event: 'PRODUCT_CREATE_FAILED',
                message: 'Failed to create product',
                context: [
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ],
                actor: auth()->user()
            );

            throw new \RuntimeException($e->getMessage(), 0, $e);
        }
    }
}
