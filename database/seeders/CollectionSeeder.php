<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Product;
use Illuminate\Database\Seeder;

class CollectionSeeder extends Seeder
{
    public function run(): void
    {
        $collections = [
            [
                'name'             => 'New Arrivals',
                'slug'             => 'new-arrivals',
                'description'      => 'The latest additions to our beauty lineup.',
                'meta_title'       => 'New Arrivals | GlamRush',
                'meta_description' => 'Discover the newest makeup, skincare and haircare products just landed at GlamRush.',
                'sort_order'       => 1,
                'image_seed'       => 'col-new-arrivals',
            ],
            [
                'name'             => 'Best Sellers',
                'slug'             => 'best-sellers',
                'description'      => 'Our most-loved products, chosen by the GlamRush community.',
                'meta_title'       => 'Best Sellers | GlamRush',
                'meta_description' => 'Shop the top-rated beauty products loved by thousands of customers.',
                'sort_order'       => 2,
                'image_seed'       => 'col-best-sellers',
            ],
            [
                'name'             => 'Summer Glow',
                'slug'             => 'summer-glow',
                'description'      => 'Radiant picks to keep you glowing all summer long.',
                'meta_title'       => 'Summer Glow | GlamRush',
                'meta_description' => 'Find bronzers, highlighters and SPF essentials for a sun-kissed look.',
                'sort_order'       => 3,
                'image_seed'       => 'col-summer-glow',
            ],
            [
                'name'             => 'Gift Sets',
                'slug'             => 'gift-sets',
                'description'      => 'Beautifully curated gift sets for every occasion.',
                'meta_title'       => 'Gift Sets | GlamRush',
                'meta_description' => 'Treat someone special with our handpicked beauty gift collections.',
                'sort_order'       => 4,
                'image_seed'       => 'col-gift-sets',
            ],
            [
                'name'             => 'Under ₦5,000',
                'slug'             => 'under-5000',
                'description'      => 'Great beauty finds that won\'t break the bank.',
                'meta_title'       => 'Under ₦5,000 | GlamRush',
                'meta_description' => 'Shop affordable makeup, skincare and hair products all under ₦5,000.',
                'sort_order'       => 5,
                'image_seed'       => 'col-budget',
            ],
        ];

        $products = Product::inRandomOrder()->take(10)->get();

        foreach ($collections as $index => $data) {
            $imageSeed = $data['image_seed'];
            unset($data['image_seed']);

            $collection = Collection::firstOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_active' => true])
            );

            try {
                if ($collection->getMedia('catalog-photos')->isEmpty()) {
                    $collection->addMediaFromUrl("https://picsum.photos/seed/{$imageSeed}/600/600")
                        ->usingName($collection->name)
                        ->toMediaCollection('catalog-photos');
                }
            } catch (\Exception $e) {
                // Skip image if URL is unreachable
            }

            // Attach a slice of products to each collection with a sort_order
            $slice = $products->slice($index * 2, 4);
            $syncData = [];
            foreach ($slice->values() as $i => $product) {
                $syncData[$product->id] = ['sort_order' => $i + 1];
            }
            if (!empty($syncData)) {
                $collection->products()->syncWithoutDetaching($syncData);
            }
        }
    }
}
