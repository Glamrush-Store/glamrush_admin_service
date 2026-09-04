<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AppRootCategorySeeder extends Seeder
{
    public function run(): void
    {
        $name = (string) config('app.root_category_name', env('APP_ROOT_CATEGORY_NAME', 'Fragrances'));
        $slug = Str::slug($name);

        Category::query()->updateOrCreate(
            ['slug' => $slug],
            [
                'name' => $name,
                'parent_id' => null,
                'is_active' => true,
                'description' => "{$name} catalog root category.",
                'meta_title' => $name,
                'meta_description' => "Shop {$name} on Glamrush.",
                'sort_order' => 0,
            ]
        );
    }
}
