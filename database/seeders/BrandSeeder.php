<?php

namespace Database\Seeders;

use App\Models\Brand;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            [
                'name'             => 'Maybelline',
                'slug'             => 'maybelline',
                'code'             => 'MYBL',
                'description'      => 'Maybe she\'s born with it — iconic drugstore beauty brand loved worldwide.',
                'meta_title'       => 'Maybelline | GlamRush',
                'meta_description' => 'Shop Maybelline makeup including foundations, mascaras and lip colours.',
                'is_active'        => true,
                'sort_order'       => 1,
                'image_seed'       => 'brand-maybelline',
            ],
            [
                'name'             => "L'Oréal Paris",
                'slug'             => 'loreal-paris',
                'code'             => 'LRLP',
                'description'      => 'Because you\'re worth it — premium beauty products accessible to all.',
                'meta_title'       => "L'Oréal Paris | GlamRush",
                'meta_description' => "Shop L'Oréal Paris skincare, haircare and makeup products.",
                'is_active'        => true,
                'sort_order'       => 2,
                'image_seed'       => 'brand-loreal',
            ],
            [
                'name'             => 'NYX Professional',
                'slug'             => 'nyx-professional',
                'code'             => 'NYXP',
                'description'      => 'Professional-grade cosmetics for artists, influencers and beauty lovers.',
                'meta_title'       => 'NYX Professional | GlamRush',
                'meta_description' => 'Find NYX Professional Makeup including setting sprays, lip liners and palettes.',
                'is_active'        => true,
                'sort_order'       => 3,
                'image_seed'       => 'brand-nyx',
            ],
            [
                'name'             => 'MAC Cosmetics',
                'slug'             => 'mac-cosmetics',
                'code'             => 'MACC',
                'description'      => 'The iconic professional makeup brand trusted by artists globally.',
                'meta_title'       => 'MAC Cosmetics | GlamRush',
                'meta_description' => 'Shop MAC Cosmetics for foundation, eyeshadow, lipstick and more.',
                'is_active'        => true,
                'sort_order'       => 4,
                'image_seed'       => 'brand-mac',
            ],
            [
                'name'             => 'Charlotte Tilbury',
                'slug'             => 'charlotte-tilbury',
                'code'             => 'CHLT',
                'description'      => 'Luxury beauty products inspired by decades of celebrity makeup expertise.',
                'meta_title'       => 'Charlotte Tilbury | GlamRush',
                'meta_description' => 'Discover Charlotte Tilbury\'s award-winning skincare and makeup collection.',
                'is_active'        => true,
                'sort_order'       => 5,
                'image_seed'       => 'brand-charlotte',
            ],
        ];

        foreach ($brands as $data) {
            $imageSeed = $data['image_seed'];
            unset($data['image_seed']);

            $brand = Brand::firstOrCreate(
                ['slug' => $data['slug']],
                $data
            );

            try {
                if ($brand->getMedia('brand_images')->isEmpty()) {
                    $brand->addMediaFromUrl("https://picsum.photos/seed/{$imageSeed}/400/400")
                        ->usingName($brand->name)
                        ->toMediaCollection('brand_images');
                }
            } catch (\Exception $e) {
                // Skip image if URL is unreachable
            }
        }
    }
}
