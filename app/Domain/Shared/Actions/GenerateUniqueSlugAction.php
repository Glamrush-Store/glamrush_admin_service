<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Domain\Shared\Actions;

use App\Models\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class GenerateUniqueSlugAction
{
    /**
     * @param  class-string<Model>  $modelClass
     */
    public function run(
        string $name,
        string $modelClass = Category::class,
        string|int|null $ignoreId = null
    ): string {
        $baseSlug = Str::slug($name) ?: Str::lower(class_basename($modelClass));
        $slug = $baseSlug;
        $counter = 2;

        while ($this->slugExists($slug, $modelClass, $ignoreId)) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function slugExists(
        string $slug,
        string $modelClass,
        string|int|null $ignoreId
    ): bool {
        $query = $modelClass::query();

        if (in_array(SoftDeletes::class, class_uses_recursive($modelClass), true)) {
            $query->withTrashed();
        }

        return $query
            ->where('slug', $slug)
            ->when($ignoreId !== null, fn ($query) => $query->whereKeyNot($ignoreId))
            ->exists();
    }
}
