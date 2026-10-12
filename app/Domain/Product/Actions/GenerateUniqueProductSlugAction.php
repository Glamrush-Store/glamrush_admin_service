<?php

namespace App\Domain\Product\Actions;

use App\Models\Product;
use Illuminate\Support\Str;

final class GenerateUniqueProductSlugAction
{
    public function run(string $name): string
    {
        $baseSlug = Str::slug($name) ?: 'product';
        $slug = $baseSlug;
        $counter = 2;

        while (Product::withTrashed()->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
