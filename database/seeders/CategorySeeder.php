<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $parents = [
            [
                'name'             => 'Makeup',
                'slug'             => 'makeup',
                'description'      => 'Full range of makeup products for every look and occasion.',
                'meta_title'       => 'Makeup | GlamRush',
                'meta_description' => 'Shop the best makeup products including foundations, lipsticks, eyeshadows and more.',
                'sort_order'       => 1,
                'image_seed'       => 'cat-makeup',
            ],
            [
                'name'             => 'Skincare',
                'slug'             => 'skincare',
                'description'      => 'Nourish and protect your skin with our premium skincare range.',
                'meta_title'       => 'Skincare | GlamRush',
                'meta_description' => 'Discover serums, moisturizers, cleansers and more for glowing skin.',
                'sort_order'       => 2,
                'image_seed'       => 'cat-skincare',
            ],
            [
                'name'             => 'Haircare',
                'slug'             => 'haircare',
                'description'      => 'Professional hair care products for all hair types.',
                'meta_title'       => 'Haircare | GlamRush',
                'meta_description' => 'Shop shampoos, conditioners, hair masks and styling products.',
                'sort_order'       => 3,
                'image_seed'       => 'cat-haircare',
            ],
            [
                'name'             => 'Fragrance',
                'slug'             => 'fragrance',
                'description'      => 'Explore our curated collection of perfumes and body mists.',
                'meta_title'       => 'Fragrance | GlamRush',
                'meta_description' => 'Find your signature scent from our selection of luxury fragrances.',
                'sort_order'       => 4,
                'image_seed'       => 'cat-fragrance',
            ],
            [
                'name'             => 'Nail Art',
                'slug'             => 'nail-art',
                'description'      => 'Everything you need for beautiful, expressive nails.',
                'meta_title'       => 'Nail Art | GlamRush',
                'meta_description' => 'Shop nail polishes, nail art tools, base coats and top coats.',
                'sort_order'       => 5,
                'image_seed'       => 'cat-nailart',
            ],
        ];

        $children = [
            [
                'parent_slug'      => 'makeup',
                'name'             => 'Lipstick',
                'slug'             => 'lipstick',
                'description'      => 'Bold, vibrant lipsticks in matte, satin and glossy finishes.',
                'meta_title'       => 'Lipstick | GlamRush',
                'meta_description' => 'Browse a wide selection of lipstick shades and formulas.',
                'sort_order'       => 1,
                'image_seed'       => 'cat-lipstick',
            ],
            [
                'parent_slug'      => 'skincare',
                'name'             => 'Moisturizers',
                'slug'             => 'moisturizers',
                'description'      => 'Hydrating moisturizers for every skin type.',
                'meta_title'       => 'Moisturizers | GlamRush',
                'meta_description' => 'Keep your skin soft and supple with our moisturizer collection.',
                'sort_order'       => 1,
                'image_seed'       => 'cat-moisturizers',
            ],
            [
                'parent_slug'      => 'haircare',
                'name'             => 'Shampoo',
                'slug'             => 'shampoo',
                'description'      => 'Gentle and nourishing shampoos for all hair types.',
                'meta_title'       => 'Shampoo | GlamRush',
                'meta_description' => 'Cleanse and refresh your hair with our premium shampoo range.',
                'sort_order'       => 1,
                'image_seed'       => 'cat-shampoo',
            ],
            [
                'parent_slug'      => 'fragrance',
                'name'             => 'Perfumes',
                'slug'             => 'perfumes',
                'description'      => 'Long-lasting perfumes for every personality and occasion.',
                'meta_title'       => 'Perfumes | GlamRush',
                'meta_description' => 'Shop designer and niche perfumes for women and men.',
                'sort_order'       => 1,
                'image_seed'       => 'cat-perfumes',
            ],
            [
                'parent_slug'      => 'nail-art',
                'name'             => 'Nail Polish',
                'slug'             => 'nail-polish',
                'description'      => 'Vibrant, chip-resistant nail polishes in hundreds of shades.',
                'meta_title'       => 'Nail Polish | GlamRush',
                'meta_description' => 'Find your perfect shade from our extensive nail polish collection.',
                'sort_order'       => 1,
                'image_seed'       => 'cat-nail-polish',
            ],
        ];

        $parentModels = [];

        foreach ($parents as $index => $data) {
            $imageSeed = $data['image_seed'];
            unset($data['image_seed']);

            $category = Category::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_active' => true])
            );

            try {
                if ($category->getMedia('catalog-photos')->isEmpty()) {
                    $category->addMediaFromUrl("https://picsum.photos/seed/{$imageSeed}/600/600")
                        ->usingName($category->name)
                        ->toMediaCollection('catalog-photos');
                }
            } catch (\Exception $e) {
                // Skip image if URL is unreachable
            }

            $parentModels[$data['slug']] = $category;
        }

        foreach ($children as $index => $data) {
            $parentSlug = $data['parent_slug'];
            $imageSeed  = $data['image_seed'];
            unset($data['parent_slug'], $data['image_seed']);

            $parent = $parentModels[$parentSlug] ?? null;

            $category = Category::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, [
                    'parent_id' => $parent?->id,
                    'is_active' => true,
                ])
            );

            try {
                if ($category->getMedia('catalog-photos')->isEmpty()) {
                    $category->addMediaFromUrl("https://picsum.photos/seed/{$imageSeed}/600/600")
                        ->usingName($category->name)
                        ->toMediaCollection('catalog-photos');
                }
            } catch (\Exception $e) {
                // Skip image if URL is unreachable
            }
        }
    }
}
