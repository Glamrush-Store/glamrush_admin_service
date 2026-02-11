<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Shared\Actions;

use App\Models\Category;
use Illuminate\Support\Str;

class GenerateUniqueSlugAction
{
    public function run(string $name, ?int $ignoreId = null): string
    {
        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 2;

        while ($this->slugExists($slug, $ignoreId)) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    protected function slugExists(string $slug, ?int $ignoreId): bool
    {
        return Category::where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists();
    }
}
