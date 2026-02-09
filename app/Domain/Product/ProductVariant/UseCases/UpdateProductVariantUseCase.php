<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\ProductVariant\UseCases;

use App\Domain\Product\ProductVariant\Actions\UpdateProductVariantAction;
use App\Domain\Product\ProductVariant\Actions\UploadVariantPhotosAction;
use App\Domain\Shared\Actions\CreateAppLogAction;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class UpdateProductVariantUseCase
{
    public function __construct(
        private UpdateProductVariantAction $updateVariant,
        private UploadVariantPhotosAction $uploadVariantPhotos,
        private CreateAppLogAction $log
    ) {}

    public function execute(ProductVariant $variant, array $data): ProductVariant
    {
        try {
            return DB::transaction(function () use ($variant, $data) {

                // If this one becomes default, unset others
                if (! empty($data['is_default']) && $data['is_default'] === true) {
                    $variant->product
                        ->variants()
                        ->where('id', '!=', $variant->id)
                        ->update(['is_default' => false]);
                }

                if (! empty($data['photos'])) {
                    $this->uploadVariantPhotos->run($variant, $data['photos']);
                }

                $this->updateVariant->run($variant, $data);

                return $variant->fresh();
            });
        } catch (\Throwable $e) {
            $this->log->run(
                level: 'error',
                event: 'PRODUCT_VARIANT_UPDATE_FAILED',
                message: 'Failed to update product variant',
                context: [
                    'data' => $data,
                    'exception' => $e->getMessage(),
                ],
                actor: auth()->user()
            );

            throw new \RuntimeException('failed to update Product Variant', 0, $e);
        }

    }
}
