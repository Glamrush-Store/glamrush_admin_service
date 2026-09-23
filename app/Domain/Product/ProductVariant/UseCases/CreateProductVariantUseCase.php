<?php

namespace App\Domain\Product\ProductVariant\UseCases;

use App\Domain\Product\Events\ProductSavedEvent;
use App\Domain\Product\ProductVariant\Actions\CreateProductVariantsAction;
use App\Domain\Product\ProductVariant\Actions\GenerateVariantSkuAction;
use App\Domain\Product\ProductVariant\Actions\UploadVariantPhotosAction;
use App\Exceptions\BusinessException;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateProductVariantUseCase
{
    public function __construct(
        private CreateProductVariantsAction $createVariant,
        private GenerateVariantSkuAction $generateVariantSku,
        private UploadVariantPhotosAction $uploadVariantPhotos,
    ) {}

    public function execute(Product $product, array $data): ProductVariant
    {
        return DB::transaction(function () use ($product, $data) {
            $product = Product::query()
                ->whereKey($product->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $product->isVariable()) {
                throw new BusinessException(
                    'Variants can only be added to variable products.',
                    [],
                    409,
                );
            }

            if (blank($product->sku)) {
                throw new BusinessException(
                    'The product must have a SKU before variants can be added.',
                    [],
                    409,
                );
            }

            $attributes = $this->normalizeAttributes($data['attributes']);

            $hasDuplicateAttributes = $product->variants()
                ->get(['attributes'])
                ->contains(fn (ProductVariant $variant) => (
                    $this->normalizeAttributes($variant->attributes ?? []) === $attributes
                ));

            if ($hasDuplicateAttributes) {
                throw new BusinessException(
                    'A variant with this attribute combination already exists.',
                    [],
                    409,
                );
            }

            $sku = $this->generateVariantSku->run($product->sku, $attributes);

            if (ProductVariant::query()->where('sku', $sku)->exists()) {
                throw new BusinessException(
                    'A variant with the generated SKU already exists.',
                    ['sku' => $sku],
                    409,
                );
            }

            $hasVariants = $product->variants()->exists();
            $isDefault = (bool) ($data['is_default'] ?? false) || ! $hasVariants;

            if ($isDefault) {
                $product->variants()->update(['is_default' => false]);
            }

            $variant = $this->createVariant->run($product, [
                'manage_stock' => true,
                'stock_quantity' => 0,
                'in_stock' => true,
                'sort_order' => ($product->variants()->max('sort_order') ?? -1) + 1,
                'status' => 'active',
                ...Arr::except($data, ['photos']),
                'sku' => $sku,
                'attributes' => $attributes,
                'is_default' => $isDefault,
            ]);

            if (! empty($data['photos'])) {
                $this->uploadVariantPhotos->run($variant, $data['photos']);
            }

            event(new ProductSavedEvent($product));

            return $variant->refresh()->load('media');
        });
    }

    private function normalizeAttributes(array $attributes): array
    {
        return collect($attributes)
            ->map(fn (array $attribute) => [
                'type' => trim($attribute['type']),
                'value' => trim($attribute['value']),
            ])
            ->sortBy([
                ['type', 'asc'],
                ['value', 'asc'],
            ])
            ->values()
            ->all();
    }
}
