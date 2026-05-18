<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    private array $categoryIds;
    private array $brandIds;
    private array $vendorIds;
    private int $skuCounter = 1;

    public function run(): void
    {
        // Use child categories for products (leaf nodes)
        $this->categoryIds = Category::whereNotNull('parent_id')->pluck('id')->all();

        // Fallback to all categories if no children exist
        if (empty($this->categoryIds)) {
            $this->categoryIds = Category::pluck('id')->all();
        }

        $this->brandIds  = Brand::pluck('id')->all();
        $this->vendorIds = Vendor::pluck('id')->all();

        $this->createSimpleProducts();
        $this->createVariableProducts();
    }

    private function createSimpleProducts(): void
    {
        $products = [
            ['name' => 'Micellar Cleansing Water 400ml',      'price' => 18.99],
            ['name' => 'Vitamin C Brightening Serum 30ml',    'price' => 45.00],
            ['name' => 'Retinol Renewal Night Cream',         'price' => 52.00],
            ['name' => 'SPF 50 Daily Sunscreen Fluid',        'price' => 28.50],
            ['name' => 'Hydrating Rose Facial Toner',         'price' => 22.00],
            ['name' => 'Rose Hip Seed Oil 30ml',              'price' => 34.99],
            ['name' => 'Purifying Charcoal Clay Mask',        'price' => 19.99],
            ['name' => 'Smoothing Argan Hair Oil',            'price' => 29.99],
            ['name' => 'Detangling Leave-In Spray',           'price' => 16.50],
            ['name' => 'Clear Nail Base Coat',                'price' => 9.99],
            ['name' => 'Waterproof Volumizing Mascara',       'price' => 24.99],
            ['name' => 'HD Translucent Setting Powder',       'price' => 32.00],
            ['name' => 'All-Day Long-Lasting Setting Spray',  'price' => 21.00],
            ['name' => 'Eyebrow Shaping Wax Pen',             'price' => 14.99],
            ['name' => 'Daily Moisturizing Lip Balm SPF 15',  'price' => 8.50],
            ['name' => 'Deep Pore Cleansing Face Scrub',      'price' => 17.99],
            ['name' => 'Nourishing Sulfate-Free Shampoo',     'price' => 19.50],
            ['name' => 'Repair & Strengthen Conditioner',     'price' => 19.50],
            ['name' => 'Gentle Eye Makeup Remover Pads',      'price' => 11.99],
            ['name' => 'Hyaluronic Acid Plumping Lip Mask',   'price' => 15.00],
        ];

        foreach ($products as $index => $data) {
            $slug    = Str::slug($data['name']) . '-' . ($index + 1);
            $product = Product::create([
                'name'              => $data['name'],
                'slug'              => $slug,
                'type'              => 'simple',
                'status'            => 'published',
                'published_at'      => now()->subDays(rand(1, 90)),
                'category_id'       => $this->randomElement($this->categoryIds),
                'brand_id'          => $this->randomElement($this->brandIds),
                'vendor_id'         => $this->randomElement($this->vendorIds),
                'short_description' => fake()->sentence(12),
                'description'       => fake()->paragraphs(3, true),
                'price'             => $data['price'],
                'sale_price'        => round($data['price'] * 0.85, 2),
                'sale_starts_at'    => now()->subDays(5),
                'sale_ends_at'      => now()->addDays(25),
                'manage_stock'      => true,
                'stock_quantity'    => rand(20, 500),
                'in_stock'          => true,
                'is_featured'       => $index < 5,
                'sort_order'        => $index + 1,
                'sequence'          => $this->nextSequence(),
                'meta_title'        => $data['name'],
                'meta_description'  => fake()->sentence(18),
                'meta_keywords'     => implode(', ', fake()->words(5)),
            ]);

            ProductVariant::create([
                'product_id'     => $product->id,
                'sku'            => 'SMPL-' . strtoupper(Str::random(6)) . '-' . $this->skuCounter,
                'is_default'     => true,
                'price'          => $data['price'],
                'sale_price'     => round($data['price'] * 0.85, 2),
                'sale_starts_at' => now()->subDays(5),
                'sale_ends_at'   => now()->addDays(25),
                'manage_stock'   => true,
                'stock_quantity' => rand(20, 500),
                'in_stock'       => true,
                'attributes'     => [],
                'sort_order'     => 0,
                'status'         => 'active',
            ]);

            $this->attachProductImage($product, "prod-simple-{$index}");
        }
    }

    private function createVariableProducts(): void
    {
        $products = [
            [
                'name'  => 'Satin Finish Lipstick',
                'price' => 22.00,
                'variants' => [
                    ['color' => 'Red Velvet',  'shade' => 'RV01'],
                    ['color' => 'Pink Sugar',  'shade' => 'PS02'],
                    ['color' => 'Nude Beige',  'shade' => 'NB03'],
                    ['color' => 'Berry Bliss', 'shade' => 'BB04'],
                    ['color' => 'Coral Kiss',  'shade' => 'CK05'],
                ],
            ],
            [
                'name'  => 'Liquid Foundation SPF 20',
                'price' => 38.00,
                'variants' => [
                    ['shade' => 'Fair Ivory',    'code' => 'N10'],
                    ['shade' => 'Light Beige',   'code' => 'N20'],
                    ['shade' => 'Medium Tan',    'code' => 'N30'],
                    ['shade' => 'Deep Mahogany', 'code' => 'N40'],
                ],
            ],
            [
                'name'  => 'Artistry Eyeshadow Palette',
                'price' => 48.00,
                'variants' => [
                    ['collection' => 'Smoky Night'],
                    ['collection' => 'Rose Gold'],
                    ['collection' => 'Earth Tones'],
                ],
            ],
            [
                'name'  => 'High-Shine Nail Polish',
                'price' => 12.50,
                'variants' => [
                    ['color' => 'Ruby Red'],
                    ['color' => 'Midnight Black'],
                    ['color' => 'Lilac Dream'],
                    ['color' => 'Coral Sun'],
                    ['color' => 'Champagne Gold'],
                ],
            ],
            [
                'name'  => "Eau de Parfum Signature",
                'price' => 85.00,
                'variants' => [
                    ['size' => '30ml'],
                    ['size' => '50ml'],
                    ['size' => '75ml'],
                    ['size' => '100ml'],
                ],
            ],
            [
                'name'  => 'Velvet Matte Blush',
                'price' => 26.00,
                'variants' => [
                    ['shade' => 'Peachy Keen'],
                    ['shade' => 'Rose Petal'],
                    ['shade' => 'Terracotta'],
                ],
            ],
            [
                'name'  => 'Tinted BB Cream SPF 30',
                'price' => 30.00,
                'variants' => [
                    ['shade' => 'Light'],
                    ['shade' => 'Light Medium'],
                    ['shade' => 'Medium'],
                    ['shade' => 'Medium Dark'],
                ],
            ],
            [
                'name'  => 'Full Coverage Concealer',
                'price' => 27.00,
                'variants' => [
                    ['shade' => 'N10 Fair'],
                    ['shade' => 'N20 Light'],
                    ['shade' => 'N30 Medium'],
                    ['shade' => 'N40 Tan'],
                    ['shade' => 'N50 Deep'],
                ],
            ],
            [
                'name'  => 'Loose Setting Powder',
                'price' => 34.00,
                'variants' => [
                    ['finish' => 'Translucent'],
                    ['finish' => 'Banana'],
                    ['finish' => 'Porcelain'],
                ],
            ],
            [
                'name'  => 'Pore-Minimizing Face Primer',
                'price' => 36.00,
                'variants' => [
                    ['type' => 'Hydrating'],
                    ['type' => 'Mattifying'],
                    ['type' => 'Illuminating'],
                    ['type' => 'Color Correcting'],
                ],
            ],
            [
                'name'  => 'Whipped Body Butter',
                'price' => 23.00,
                'variants' => [
                    ['scent' => 'Vanilla Bean'],
                    ['scent' => 'Rose Garden'],
                    ['scent' => 'Citrus Burst'],
                ],
            ],
            [
                'name'  => 'Gentle Foaming Face Wash',
                'price' => 18.00,
                'variants' => [
                    ['skin_type' => 'Oily Skin'],
                    ['skin_type' => 'Dry Skin'],
                    ['skin_type' => 'Sensitive Skin'],
                ],
            ],
            [
                'name'  => 'Balancing Facial Toner',
                'price' => 21.00,
                'variants' => [
                    ['formula' => 'Hydrating'],
                    ['formula' => 'Clarifying'],
                    ['formula' => 'Brightening'],
                ],
            ],
            [
                'name'  => 'Extreme Volume Mascara',
                'price' => 25.00,
                'variants' => [
                    ['formula' => 'Extreme Volume'],
                    ['formula' => 'Length & Lift'],
                    ['formula' => 'Waterproof Curl'],
                ],
            ],
            [
                'name'  => 'Precision Liquid Eyeliner',
                'price' => 16.00,
                'variants' => [
                    ['color' => 'Intense Black'],
                    ['color' => 'Warm Brown'],
                    ['color' => 'Deep Navy'],
                ],
            ],
            [
                'name'  => 'Defining Lip Liner',
                'price' => 14.00,
                'variants' => [
                    ['shade' => 'Nude'],
                    ['shade' => 'Pink'],
                    ['shade' => 'Red'],
                    ['shade' => 'Berry'],
                ],
            ],
            [
                'name'  => 'Intensive Repair Hair Mask',
                'price' => 28.00,
                'variants' => [
                    ['hair_type' => 'Damaged Hair'],
                    ['hair_type' => 'Dry Hair'],
                    ['hair_type' => 'Colored Hair'],
                ],
            ],
            [
                'name'  => 'Brightening Sheet Mask',
                'price' => 6.50,
                'variants' => [
                    ['formula' => 'Vitamin C'],
                    ['formula' => 'Hyaluronic Acid'],
                    ['formula' => 'Charcoal'],
                    ['formula' => 'Rose Extract'],
                ],
            ],
            [
                'name'  => 'Advanced Face Serum',
                'price' => 55.00,
                'variants' => [
                    ['benefit' => 'Anti-Aging'],
                    ['benefit' => 'Brightening'],
                    ['benefit' => 'Deep Hydration'],
                ],
            ],
            [
                'name'  => 'Balancing Face Cream',
                'price' => 44.00,
                'variants' => [
                    ['skin_type' => 'Oily Skin'],
                    ['skin_type' => 'Combination Skin'],
                    ['skin_type' => 'Dry Skin'],
                ],
            ],
            [
                'name'  => 'Contour & Sculpt Stick',
                'price' => 29.00,
                'variants' => [
                    ['shade' => 'Light'],
                    ['shade' => 'Medium'],
                    ['shade' => 'Deep'],
                ],
            ],
            [
                'name'  => 'Glow Highlighter Powder',
                'price' => 32.00,
                'variants' => [
                    ['shade' => 'Pearl Glow'],
                    ['shade' => 'Rose Gold'],
                    ['shade' => 'Warm Bronze'],
                ],
            ],
            [
                'name'  => 'Permanent Hair Color',
                'price' => 18.50,
                'variants' => [
                    ['shade' => 'Midnight Black'],
                    ['shade' => 'Dark Brown'],
                    ['shade' => 'Chestnut'],
                    ['shade' => 'Auburn'],
                    ['shade' => 'Strawberry Blonde'],
                ],
            ],
            [
                'name'  => 'Exfoliating Body Scrub',
                'price' => 20.00,
                'variants' => [
                    ['scent' => 'Coffee & Mint'],
                    ['scent' => 'Lavender'],
                    ['scent' => 'Coconut Lime'],
                    ['scent' => 'Honey Almond'],
                ],
            ],
            [
                'name'  => 'Smoky Eyeshadow Palette',
                'price' => 42.00,
                'variants' => [
                    ['collection' => 'Classic Smoke'],
                    ['collection' => 'Warm Copper'],
                    ['collection' => 'Cool Taupe'],
                ],
            ],
            [
                'name'  => 'Sun-Kissed Bronzer',
                'price' => 30.00,
                'variants' => [
                    ['shade' => 'Light Bronze'],
                    ['shade' => 'Medium Tan'],
                    ['shade' => 'Deep Glow'],
                ],
            ],
            [
                'name'  => 'Micro Precision Brow Pencil',
                'price' => 19.00,
                'variants' => [
                    ['shade' => 'Blonde'],
                    ['shade' => 'Taupe'],
                    ['shade' => 'Dark Brown'],
                    ['shade' => 'Black'],
                ],
            ],
            [
                'name'  => 'Plumping Lip Gloss',
                'price' => 17.00,
                'variants' => [
                    ['shade' => 'Nude Shimmer'],
                    ['shade' => 'Pink Pop'],
                    ['shade' => 'Red Velvet'],
                    ['shade' => 'Clear Gloss'],
                ],
            ],
            [
                'name'  => 'Refreshing Setting Mist',
                'price' => 19.00,
                'variants' => [
                    ['size' => '50ml'],
                    ['size' => '100ml'],
                    ['size' => '200ml'],
                ],
            ],
            [
                'name'  => 'Buildable Cheek Tint',
                'price' => 24.00,
                'variants' => [
                    ['shade' => 'Sweet Peach'],
                    ['shade' => 'Berry Flush'],
                    ['shade' => 'Rose Blush'],
                ],
            ],
        ];

        foreach ($products as $pIndex => $data) {
            $slug    = Str::slug($data['name']) . '-' . ($pIndex + 21);
            $product = Product::create([
                'name'              => $data['name'],
                'slug'              => $slug,
                'type'              => 'variable',
                'status'            => 'published',
                'published_at'      => now()->subDays(rand(1, 90)),
                'category_id'       => $this->randomElement($this->categoryIds),
                'brand_id'          => $this->randomElement($this->brandIds),
                'vendor_id'         => $this->randomElement($this->vendorIds),
                'short_description' => fake()->sentence(12),
                'description'       => fake()->paragraphs(3, true),
                'price'             => $data['price'],
                'manage_stock'      => true,
                'stock_quantity'    => 0,
                'in_stock'          => true,
                'is_featured'       => $pIndex < 5,
                'sort_order'        => $pIndex + 21,
                'sequence'          => $this->nextSequence(),
                'meta_title'        => $data['name'],
                'meta_description'  => fake()->sentence(18),
                'meta_keywords'     => implode(', ', fake()->words(5)),
            ]);

            foreach ($data['variants'] as $vIndex => $attributes) {
                $variantPrice = round($data['price'] * fake()->randomFloat(2, 0.9, 1.3), 2);

                $variant = ProductVariant::create([
                    'product_id'     => $product->id,
                    'sku'            => 'VAR-' . strtoupper(Str::random(6)) . '-' . $this->skuCounter,
                    'is_default'     => $vIndex === 0,
                    'price'          => $variantPrice,
                    'sale_price'     => rand(0, 1) ? round($variantPrice * 0.85, 2) : null,
                    'sale_starts_at' => now()->subDays(5),
                    'sale_ends_at'   => now()->addDays(25),
                    'manage_stock'   => true,
                    'stock_quantity' => rand(10, 300),
                    'in_stock'       => true,
                    'attributes'     => array_map(
                        fn($type, $value) => ['type' => $type, 'value' => $value],
                        array_keys($attributes),
                        array_values($attributes)
                    ),
                    'sort_order'     => $vIndex,
                    'status'         => 'active',
                ]);

                $this->attachVariantImage($variant, "var-{$pIndex}-{$vIndex}");
            }

            $this->attachProductImage($product, "prod-var-{$pIndex}");
        }
    }

    private function attachProductImage(Product $product, string $seed): void
    {
        try {
            $product->addMediaFromUrl("https://picsum.photos/seed/{$seed}/800/800")
                ->usingName($product->name)
                ->toMediaCollection('catalog-photos');
        } catch (\Exception $e) {
            // Skip image if URL is unreachable
        }
    }

    private function attachVariantImage(ProductVariant $variant, string $seed): void
    {
        try {
            $variant->addMediaFromUrl("https://picsum.photos/seed/{$seed}/600/600")
                ->usingName("Variant {$variant->sku}")
                ->toMediaCollection('catalog-photos');
        } catch (\Exception $e) {
            // Skip image if URL is unreachable
        }
    }

    private function randomElement(array $array): mixed
    {
        return $array[array_rand($array)];
    }

    private function nextSequence(): string
    {
        return (string) (10000 + $this->skuCounter++);
    }
}
