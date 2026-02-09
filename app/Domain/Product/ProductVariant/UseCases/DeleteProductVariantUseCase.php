<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Product\ProductVariant\UseCases;

use App\Domain\Product\ProductVariant\Actions\DeleteProductVariantAction;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;

class DeleteProductVariantUseCase
{
    public function __construct(
        private DeleteProductVariantAction $deleteVariant
    ) {}

    public function execute(ProductVariant $variant): void
    {
        DB::transaction(function () use ($variant) {

            $product = $variant->product;
            $wasDefault = $variant->is_default;

            $this->deleteVariant->run($variant);

            // 👑 Promote a new default if needed
            if ($wasDefault) {
                $newDefault = $product->variants()
                    ->orderBy('sort_order')
                    ->first();

                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                }
            }
        });
    }
}
