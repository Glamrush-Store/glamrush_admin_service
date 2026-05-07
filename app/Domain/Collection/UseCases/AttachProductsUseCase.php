<?php

namespace App\Domain\Collection\UseCases;

use App\Models\Collection;

class AttachProductsUseCase
{
    /**
     * Sync products on a collection.
     * $products is an array of ['id' => ..., 'sort_order' => ...] or just product IDs.
     */
    public function run(Collection $collection, array $products): Collection
    {
        $syncData = [];
        foreach ($products as $product) {
            if (is_array($product)) {
                $syncData[$product['id']] = ['sort_order' => $product['sort_order'] ?? 0];
            } else {
                $syncData[$product] = ['sort_order' => 0];
            }
        }

        $collection->products()->sync($syncData);

        return $collection->load('products');
    }
}
