<?php

namespace App\Domain\Product\Actions;

use App\Models\Product;
use DomainException;
use Illuminate\Support\Str;

class SyncProductCategoriesAction
{
    public function run(Product $product, array $categoryIds, ?string $primaryCategoryId = null, array $sequences = []): void
    {
        $categoryIds = collect($categoryIds)
            ->filter(fn ($id) => is_string($id) && trim($id) !== '')
            ->map(fn ($id) => (string) $id)
            ->unique()
            ->values();

        if ($categoryIds->isEmpty()) {
            throw new DomainException('A product must belong to at least one category.');
        }

        $primaryCategoryId = $primaryCategoryId ?: $categoryIds->first();

        if (! $categoryIds->contains($primaryCategoryId)) {
            throw new DomainException('The primary category must be included in category_ids.');
        }

        $sync = [];
        foreach ($categoryIds as $index => $categoryId) {
            $sync[$categoryId] = [
                'id' => (string) Str::ulid(),
                'is_primary' => $categoryId === $primaryCategoryId,
                'sequence' => (int) ($sequences[$categoryId] ?? $sequences[$index] ?? $product->sort_order ?? $index + 1),
            ];
        }

        $product->categories()->sync($sync);
    }
}

