<?php

namespace Database\Seeders;

use App\Domain\Storefront\Enums\HomepageSectionType;
use App\Models\AttributeType;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Collection;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\SkuAttributeCode;
use App\Models\StorefrontCampaign;
use App\Models\StorefrontHomepageSection;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AppDataSeeder extends Seeder
{
    // Set to false to seed product/catalog data without attaching product images.
    private const SEED_PRODUCT_IMAGES = false;

    public function run(): void
    {
        DB::transaction(function () {
            $this->seedAttributes();
            [$vendor, $brand] = $this->seedProductOwners();
            $categories = $this->seedCategories();
            $this->resetSeededProductSequences();
            $products = $this->seedProducts($categories, $vendor, $brand);
            $collections = $this->seedCollections($products);
            $this->seedStorefrontMerchandising($products, $collections);
        });
    }

    private function seedAttributes(): void
    {
        $types = [
            ['category' => 'Fragrance', 'value' => 'volume', 'label' => 'Volume', 'display_type' => 'select'],
            ['category' => 'Fragrance', 'value' => 'concentration', 'label' => 'Concentration', 'display_type' => 'select'],
            ['category' => 'Fragrance', 'value' => 'scent_family', 'label' => 'Scent family', 'display_type' => 'select'],
            ['category' => 'Universal', 'value' => 'gender', 'label' => 'Gender', 'display_type' => 'select'],
            ['category' => 'Skincare', 'value' => 'skin_type', 'label' => 'Skin type', 'display_type' => 'select'],
            ['category' => 'Cosmetics', 'value' => 'shade', 'label' => 'Shade', 'display_type' => 'swatch'],
            ['category' => 'Cosmetics', 'value' => 'finish', 'label' => 'Finish', 'display_type' => 'select'],
            ['category' => 'Haircare', 'value' => 'hair_type', 'label' => 'Hair type', 'display_type' => 'select'],
        ];

        foreach ($types as $type) {
            AttributeType::updateOrCreate(['value' => $type['value']], $type);
        }

        $values = [
            ['type' => 'volume', 'value' => '30ml', 'code' => '30ML'],
            ['type' => 'volume', 'value' => '50ml', 'code' => '50ML'],
            ['type' => 'volume', 'value' => '100ml', 'code' => '100ML'],
            ['type' => 'concentration', 'value' => 'Perfume Oil', 'code' => 'OIL'],
            ['type' => 'concentration', 'value' => 'Eau de Parfum', 'code' => 'EDP'],
            ['type' => 'concentration', 'value' => 'Body Mist', 'code' => 'MIST'],
            ['type' => 'scent_family', 'value' => 'Amber', 'code' => 'AMBR'],
            ['type' => 'scent_family', 'value' => 'Floral', 'code' => 'FLRL'],
            ['type' => 'scent_family', 'value' => 'Woody', 'code' => 'WOOD'],
            ['type' => 'gender', 'value' => 'Unisex', 'code' => 'UNI'],
            ['type' => 'skin_type', 'value' => 'All skin types', 'code' => 'ALL'],
            ['type' => 'skin_type', 'value' => 'Dry skin', 'code' => 'DRY'],
            ['type' => 'skin_type', 'value' => 'Oily skin', 'code' => 'OILY'],
            ['type' => 'skin_type', 'value' => 'Sensitive skin', 'code' => 'SENS'],
            ['type' => 'shade', 'value' => 'Nude', 'code' => 'NUDE'],
            ['type' => 'shade', 'value' => 'Rose', 'code' => 'ROSE'],
            ['type' => 'shade', 'value' => 'Deep', 'code' => 'DEEP'],
            ['type' => 'finish', 'value' => 'Matte', 'code' => 'MAT'],
            ['type' => 'finish', 'value' => 'Satin', 'code' => 'SAT'],
            ['type' => 'finish', 'value' => 'Radiant', 'code' => 'RAD'],
            ['type' => 'hair_type', 'value' => 'All hair types', 'code' => 'ALLH'],
            ['type' => 'hair_type', 'value' => 'Curly hair', 'code' => 'CURL'],
            ['type' => 'hair_type', 'value' => 'Dry hair', 'code' => 'DRYH'],
        ];

        foreach ($values as $value) {
            SkuAttributeCode::updateOrCreate(
                ['type' => $value['type'], 'value' => $value['value']],
                [...$value, 'display_type' => 'select', 'meta' => [], 'is_active' => true]
            );
        }
    }

    /** @return array{Vendor, Brand} */
    private function seedProductOwners(): array
    {
        $vendor = Vendor::updateOrCreate(
            ['email' => 'catalogue@glamrush.test'],
            [
                'name' => 'GlamRush Catalogue',
                'business_name' => 'GlamRush Beauty Limited',
                'phone' => '+2348000000000',
                'password' => Hash::make('password'),
                'code' => 'GLAMRUSH',
                'is_active' => true,
                'city' => 'Lagos',
                'state' => 'Lagos',
                'country' => 'NG',
            ]
        );

        $brand = Brand::updateOrCreate(
            ['slug' => 'glamrush-signature'],
            [
                'name' => 'GlamRush Signature',
                'code' => 'GRS',
                'description' => 'GlamRush signature beauty and fragrance products.',
                'is_active' => true,
                'sort_order' => 1,
            ]
        );

        return [$vendor, $brand];
    }

    private function resetSeededProductSequences(): void
    {
        Product::query()
            ->whereIn('slug', $this->seededProductSlugs())
            ->get(['id', 'sequence'])
            ->values()
            ->each(function (Product $product, int $index): void {
                $product->forceFill(['sequence' => 1_000_000 + $index])->save();
            });
    }

    private function seededProductSlugs(): array
    {
        return [
            'midnight-amber-perfume-oil',
            'velvet-oud-perfume-oil',
            'after-dark-eau-de-parfum',
            'white-bloom-eau-de-parfum',
            'sunlit-neroli-body-mist',
            'cloud-cream-moisturizer',
            ...array_map(fn (int $index): string => "catalog-product-{$index}", range(1, 144)),
        ];
    }

    /** @return array<string, Category> */
    private function seedCategories(): array
    {
        $definitions = [
            ['name' => 'Fragrances', 'slug' => 'fragrances', 'parent' => null, 'order' => 1],
            ['name' => 'Perfume Oils', 'slug' => 'perfume-oils', 'parent' => 'fragrances', 'order' => 1],
            ['name' => 'Eau de Parfum', 'slug' => 'eau-de-parfum', 'parent' => 'fragrances', 'order' => 2],
            ['name' => 'Body Mists', 'slug' => 'body-mists', 'parent' => 'fragrances', 'order' => 3],
            ['name' => 'Fragrance Gift Sets', 'slug' => 'fragrance-gift-sets', 'parent' => 'fragrances', 'order' => 4],
            ['name' => 'Skincare', 'slug' => 'skincare', 'parent' => null, 'order' => 2],
            ['name' => 'Moisturizers', 'slug' => 'moisturizers', 'parent' => 'skincare', 'order' => 1],
            ['name' => 'Cleansers', 'slug' => 'cleansers', 'parent' => 'skincare', 'order' => 2],
            ['name' => 'Serums', 'slug' => 'serums', 'parent' => 'skincare', 'order' => 3],
            ['name' => 'Sunscreen', 'slug' => 'sunscreen', 'parent' => 'skincare', 'order' => 4],
            ['name' => 'Face Masks', 'slug' => 'face-masks', 'parent' => 'skincare', 'order' => 5],
            ['name' => 'Makeup', 'slug' => 'makeup', 'parent' => null, 'order' => 3],
            ['name' => 'Lip Colour', 'slug' => 'lip-colour', 'parent' => 'makeup', 'order' => 1],
            ['name' => 'Foundation', 'slug' => 'foundation', 'parent' => 'makeup', 'order' => 2],
            ['name' => 'Eye Makeup', 'slug' => 'eye-makeup', 'parent' => 'makeup', 'order' => 3],
            ['name' => 'Blush and Bronzer', 'slug' => 'blush-bronzer', 'parent' => 'makeup', 'order' => 4],
            ['name' => 'Haircare', 'slug' => 'haircare', 'parent' => null, 'order' => 4],
            ['name' => 'Shampoo', 'slug' => 'shampoo', 'parent' => 'haircare', 'order' => 1],
            ['name' => 'Conditioner', 'slug' => 'conditioner', 'parent' => 'haircare', 'order' => 2],
            ['name' => 'Hair Treatments', 'slug' => 'hair-treatments', 'parent' => 'haircare', 'order' => 3],
            ['name' => 'Hair Styling', 'slug' => 'hair-styling', 'parent' => 'haircare', 'order' => 4],
            ['name' => 'Bath and Body', 'slug' => 'bath-body', 'parent' => null, 'order' => 5],
            ['name' => 'Body Wash', 'slug' => 'body-wash', 'parent' => 'bath-body', 'order' => 1],
            ['name' => 'Body Lotions', 'slug' => 'body-lotions', 'parent' => 'bath-body', 'order' => 2],
            ['name' => 'Hand Care', 'slug' => 'hand-care', 'parent' => 'bath-body', 'order' => 3],
            ['name' => 'Body Scrubs', 'slug' => 'body-scrubs', 'parent' => 'bath-body', 'order' => 4],
        ];

        $categories = [];
        foreach ($definitions as $index => $definition) {
            $parent = $definition['parent'] ? $categories[$definition['parent']] : null;
            $category = Category::updateOrCreate(
                ['slug' => $definition['slug']],
                [
                    'name' => $definition['name'],
                    'parent_id' => $parent?->id,
                    'description' => "Shop {$definition['name']} at GlamRush.",
                    'meta_title' => "{$definition['name']} | GlamRush",
                    'meta_description' => "Discover curated {$definition['name']} from GlamRush.",
                    'sort_order' => $definition['order'],
                    'is_active' => true,
                ]
            );
            $this->attachSeedImage($category, $definition['slug'], $index);
            $categories[$definition['slug']] = $category;
        }

        return $categories;
    }

    /** @return array<string, Product> */
    private function seedProducts(array $categories, Vendor $vendor, Brand $brand): array
    {
        $definitions = [
            'midnight-amber-perfume-oil' => [
                'name' => 'Midnight Amber Perfume Oil', 'category' => 'perfume-oils', 'price' => 18500, 'featured' => true,
                'variants' => [
                    ['sku' => 'GRS-MAPO-30ML', 'price' => 18500, 'attributes' => [['type' => 'volume', 'value' => '30ml'], ['type' => 'concentration', 'value' => 'Perfume Oil'], ['type' => 'scent_family', 'value' => 'Amber']]],
                    ['sku' => 'GRS-MAPO-50ML', 'price' => 26500, 'attributes' => [['type' => 'volume', 'value' => '50ml'], ['type' => 'concentration', 'value' => 'Perfume Oil'], ['type' => 'scent_family', 'value' => 'Amber']]],
                ],
            ],
            'velvet-oud-perfume-oil' => [
                'name' => 'Velvet Oud Perfume Oil', 'category' => 'perfume-oils', 'price' => 21000, 'featured' => true,
                'variants' => [
                    ['sku' => 'GRS-VOPO-30ML', 'price' => 21000, 'attributes' => [['type' => 'volume', 'value' => '30ml'], ['type' => 'concentration', 'value' => 'Perfume Oil'], ['type' => 'scent_family', 'value' => 'Woody']]],
                ],
            ],
            'after-dark-eau-de-parfum' => [
                'name' => 'After Dark Eau de Parfum', 'category' => 'eau-de-parfum', 'price' => 42500, 'featured' => true,
                'variants' => [
                    ['sku' => 'GRS-ADED-50ML', 'price' => 42500, 'attributes' => [['type' => 'volume', 'value' => '50ml'], ['type' => 'concentration', 'value' => 'Eau de Parfum'], ['type' => 'scent_family', 'value' => 'Amber']]],
                    ['sku' => 'GRS-ADED-100ML', 'price' => 69000, 'attributes' => [['type' => 'volume', 'value' => '100ml'], ['type' => 'concentration', 'value' => 'Eau de Parfum'], ['type' => 'scent_family', 'value' => 'Amber']]],
                ],
            ],
            'white-bloom-eau-de-parfum' => [
                'name' => 'White Bloom Eau de Parfum', 'category' => 'eau-de-parfum', 'price' => 39000, 'featured' => false,
                'variants' => [
                    ['sku' => 'GRS-WBED-50ML', 'price' => 39000, 'attributes' => [['type' => 'volume', 'value' => '50ml'], ['type' => 'concentration', 'value' => 'Eau de Parfum'], ['type' => 'scent_family', 'value' => 'Floral']]],
                ],
            ],
            'sunlit-neroli-body-mist' => [
                'name' => 'Sunlit Neroli Body Mist', 'category' => 'body-mists', 'price' => 12500, 'sale_price' => 9900, 'featured' => false,
                'variants' => [
                    ['sku' => 'GRS-SNBM-100ML', 'price' => 12500, 'sale_price' => 9900, 'attributes' => [['type' => 'volume', 'value' => '100ml'], ['type' => 'concentration', 'value' => 'Body Mist'], ['type' => 'scent_family', 'value' => 'Floral']]],
                ],
            ],
            'cloud-cream-moisturizer' => [
                'name' => 'Cloud Cream Moisturizer', 'category' => 'moisturizers', 'price' => 15500, 'featured' => true,
                'variants' => [
                    ['sku' => 'GRS-CCM-50ML', 'price' => 15500, 'attributes' => [['type' => 'volume', 'value' => '50ml'], ['type' => 'skin_type', 'value' => 'All skin types']]],
                ],
            ],
        ];

        $products = [];
        foreach ($definitions as $slug => $definition) {
            $order = count($products);
            $salePrice = $definition['sale_price'] ?? null;
            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'sequence' => $order + 1,
                    'type' => count($definition['variants']) > 1 ? 'variable' : 'simple',
                    'status' => 'published',
                    'published_at' => now()->subDays(count($definitions) - $order),
                    'brand_id' => $brand->id,
                    'vendor_id' => $vendor->id,
                    'short_description' => "A curated {$definition['name']} from GlamRush.",
                    'description' => "{$definition['name']} is part of the seeded GlamRush catalogue for local development and storefront demonstrations.",
                    'price' => $definition['price'],
                    'sale_price' => $salePrice,
                    'sale_starts_at' => $salePrice ? now()->subDay() : null,
                    'sale_ends_at' => $salePrice ? now()->addMonth() : null,
                    'manage_stock' => true,
                    'stock_quantity' => 50,
                    'in_stock' => true,
                    'is_featured' => $definition['featured'],
                    'sort_order' => $order + 1,
                    'meta_title' => $definition['name'],
                    'meta_description' => "Shop {$definition['name']} at GlamRush.",
                ]
            );

            $this->syncProductCategory($product, $categories[$definition['category']]->id, $order + 1);

            $variantSkus = [];
            foreach ($definition['variants'] as $variantOrder => $variantDefinition) {
                $variantSkus[] = $variantDefinition['sku'];
                ProductVariant::updateOrCreate(
                    ['sku' => $variantDefinition['sku']],
                    [
                        'product_id' => $product->id,
                        'is_default' => $variantOrder === 0,
                        'price' => $variantDefinition['price'],
                        'sale_price' => $variantDefinition['sale_price'] ?? null,
                        'sale_starts_at' => isset($variantDefinition['sale_price']) ? now()->subDay() : null,
                        'sale_ends_at' => isset($variantDefinition['sale_price']) ? now()->addMonth() : null,
                        'manage_stock' => true,
                        'stock_quantity' => 25,
                        'in_stock' => true,
                        'attributes' => $variantDefinition['attributes'],
                        'sort_order' => $variantOrder,
                        'status' => 'active',
                    ]
                );
            }
            ProductVariant::where('product_id', $product->id)->whereNotIn('sku', $variantSkus)->delete();
            $this->attachSeedImage($product, $definition['category'], $order);
            $products[$slug] = $product;
        }

        $this->seedGeneratedProducts($products, $categories, $vendor, $brand);

        return $products;
    }

    /** @param array<string, Product> $products */
    private function seedGeneratedProducts(array &$products, array $categories, Vendor $vendor, Brand $brand): void
    {
        $categorySlugs = [
            'perfume-oils', 'eau-de-parfum', 'body-mists', 'fragrance-gift-sets',
            'moisturizers', 'cleansers', 'serums', 'sunscreen', 'face-masks',
            'lip-colour', 'foundation', 'eye-makeup', 'blush-bronzer',
            'shampoo', 'conditioner', 'hair-treatments', 'hair-styling',
            'body-wash', 'body-lotions', 'hand-care', 'body-scrubs',
        ];
        $adjectives = ['Velvet', 'Radiant', 'Luminous', 'Silken', 'Botanical', 'Golden', 'Pure', 'Midnight', 'Cloud', 'Satin', 'Fresh', 'Nourishing'];
        $nouns = ['Essence', 'Ritual', 'Elixir', 'Veil', 'Glow', 'Reserve', 'Blend', 'Complex', 'Mist', 'Balm', 'Treatment', 'Collection'];
        $makeupCategories = ['lip-colour', 'foundation', 'eye-makeup', 'blush-bronzer'];
        $hairCategories = ['shampoo', 'conditioner', 'hair-treatments', 'hair-styling'];
        $shades = ['Nude', 'Rose', 'Deep'];
        $volumes = ['30ml', '50ml', '100ml'];

        // The six hand-authored products above include two variable products.
        // Generate 144 more products, of which 48 are variable, for totals of
        // 150 products and 50 variable products.
        $baseSequence = count($products);
        for ($index = 1; $index <= 144; $index++) {
            $number = $index;
            $sequence = $baseSequence + $index;
            $slug = "catalog-product-{$number}";
            $categorySlug = $categorySlugs[($index - 1) % count($categorySlugs)];
            $isVariable = $index <= 48;
            $name = $adjectives[($index - 1) % count($adjectives)].' '
                .$nouns[(int) floor(($index - 1) / count($adjectives)) % count($nouns)]." {$number}";
            $price = 7500 + (($index * 1375) % 48000);
            $salePrice = $index % 6 === 0 ? (int) round($price * 0.8) : null;

            $product = Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $name,
                    'sequence' => $sequence,
                    'type' => $isVariable ? 'variable' : 'simple',
                    'status' => 'published',
                    'published_at' => now()->subDays($index % 120),
                    'brand_id' => $brand->id,
                    'vendor_id' => $vendor->id,
                    'short_description' => "{$name}, selected for the GlamRush catalogue.",
                    'description' => "{$name} is deterministic seeded catalogue data for product listing, filtering, collection, and merchandising development.",
                    'price' => $price,
                    'sale_price' => $salePrice,
                    'sale_starts_at' => $salePrice ? now()->subDays(2) : null,
                    'sale_ends_at' => $salePrice ? now()->addDays(30) : null,
                    'manage_stock' => true,
                    'stock_quantity' => 20 + ($index % 80),
                    'in_stock' => true,
                    'is_featured' => $index % 9 === 0,
                    'sort_order' => 100 + $index,
                    'meta_title' => $name,
                    'meta_description' => "Shop {$name} at GlamRush.",
                ]
            );

            $this->syncProductCategory($product, $categories[$categorySlug]->id, $sequence);

            $variantCount = $isVariable ? 3 : 1;

            $variantSkus = [];
            for ($variantOrder = 0; $variantOrder < $variantCount; $variantOrder++) {
                $option = in_array($categorySlug, $makeupCategories, true) ? $shades[$variantOrder] : $volumes[$variantOrder];
                $attributeType = in_array($categorySlug, $makeupCategories, true) ? 'shade' : 'volume';
                $attributes = [['type' => $attributeType, 'value' => $option]];
                if (in_array($categorySlug, $hairCategories, true)) {
                    $attributes[] = ['type' => 'hair_type', 'value' => 'All hair types'];
                }

                $sku = "APP-{$number}-".strtoupper(str_replace(' ', '', $option));
                $variantSkus[] = $sku;
                $variantPrice = $price + ($variantOrder * 2500);
                ProductVariant::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'product_id' => $product->id,
                        'is_default' => $variantOrder === 0,
                        'price' => $variantPrice,
                        'sale_price' => $salePrice ? $salePrice + ($variantOrder * 2000) : null,
                        'sale_starts_at' => $salePrice ? now()->subDays(2) : null,
                        'sale_ends_at' => $salePrice ? now()->addDays(30) : null,
                        'manage_stock' => true,
                        'stock_quantity' => 10 + (($index + $variantOrder) % 40),
                        'in_stock' => true,
                        'attributes' => $attributes,
                        'sort_order' => $variantOrder,
                        'status' => 'active',
                    ]
                );
            }

            ProductVariant::where('product_id', $product->id)->whereNotIn('sku', $variantSkus)->delete();
            $this->attachSeedImage($product, $categorySlug, $index);
            $products[$slug] = $product;
        }
    }

    private function attachSeedImage(Product|Category $model, string $categorySlug, int $index): void
    {
        if ($model instanceof Product && ! self::SEED_PRODUCT_IMAGES) {
            return;
        }

        if ($model->getMedia('catalog-photos')->isNotEmpty()) {
            return;
        }

        $pool = match ($categorySlug) {
            'perfume-oils' => [
                'perfume-oils/perfume-oil-01.jpg',
                'perfume-oils/perfume-oil-02.jpg',
                'perfume-oils/perfume-oil-03.jpg',
            ],
            'fragrances', 'eau-de-parfum', 'body-mists', 'fragrance-gift-sets' => [
                'perfumes/perfume-01.jpg',
                'perfumes/perfume-02.jpg',
                'perfumes/perfume-03.jpg',
                'perfumes/perfume-04.jpg',
            ],
            default => [
                'home-scents/home-scent-01.jpg',
                'home-scents/home-scent-02.jpg',
                'home-scents/home-scent-03.jpg',
            ],
        };

        $asset = database_path('seeders/assets/'.$pool[$index % count($pool)]);
        if (! is_file($asset)) {
            $this->command?->warn("Seed image not found: {$asset}");

            return;
        }

        $model->addMedia($asset)
            ->preservingOriginal()
            ->usingName($model->name)
            ->toMediaCollection('catalog-photos');
    }

    /** @return array<string, Collection> */
    private function seedCollections(array $products): array
    {
        $definitions = [
            'midnight-edit' => ['name' => 'The Midnight Edit', 'products' => ['midnight-amber-perfume-oil', 'after-dark-eau-de-parfum', 'velvet-oud-perfume-oil']],
            'new-arrivals' => ['name' => 'New Arrivals', 'products' => ['sunlit-neroli-body-mist', 'white-bloom-eau-de-parfum', 'cloud-cream-moisturizer']],
        ];

        $collections = [];
        foreach ($definitions as $slug => $definition) {
            $order = count($collections);
            $collection = Collection::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $definition['name'],
                    'description' => "A curated {$definition['name']} collection.",
                    'is_active' => true,
                    'sort_order' => $order + 1,
                    'meta_title' => "{$definition['name']} | GlamRush",
                    'meta_description' => "Shop {$definition['name']} at GlamRush.",
                ]
            );
            $collection->products()->sync(collect($definition['products'])->mapWithKeys(
                fn (string $slug, int $index) => [$products[$slug]->id => ['sort_order' => $index + 1]]
            )->all());
            $collections[$slug] = $collection;
        }

        return $collections;
    }

    private function seedStorefrontMerchandising(array $products, array $collections): void
    {
        $userId = User::query()->value('id');

        $campaigns = [
            ['storefront' => 'fragrances', 'name' => 'The Midnight Edit', 'eyebrow' => 'After-dark fragrances', 'title' => 'Leave a trace.', 'description' => 'A magnetic fragrance collection for after dark.', 'cta' => 'Shop the campaign', 'url' => '/collections/midnight-edit', 'priority' => 100, 'active' => true, 'starts' => now()->subDay(), 'ends' => now()->addMonths(3)],
            ['storefront' => 'fragrances', 'name' => 'Perfume Oil Spotlight', 'eyebrow' => 'Concentrated character', 'title' => 'A little goes a long way.', 'description' => 'Discover long-lasting perfume oils in memorable scent families.', 'cta' => 'Shop perfume oils', 'url' => '/categories/perfume-oils', 'priority' => 80, 'active' => true, 'starts' => null, 'ends' => null],
            ['storefront' => 'fragrances', 'name' => 'Fragrance Everyday', 'eyebrow' => 'Find your signature', 'title' => 'Scent, remembered.', 'description' => 'Everyday fragrances selected by GlamRush.', 'cta' => 'Shop fragrances', 'url' => '/categories/fragrances', 'priority' => 10, 'active' => true, 'starts' => null, 'ends' => null],
            ['storefront' => 'fragrances', 'name' => 'Holiday Gifting Preview', 'eyebrow' => 'Coming soon', 'title' => 'Give something unforgettable.', 'description' => 'A future campaign for fragrance gifting season.', 'cta' => 'Preview gift sets', 'url' => '/categories/fragrance-gift-sets', 'priority' => 200, 'active' => true, 'starts' => now()->addDays(14), 'ends' => now()->addMonths(4)],
            ['storefront' => 'fragrances', 'name' => 'Summer Scent Archive', 'eyebrow' => 'Seasonal scents', 'title' => 'The summer edit.', 'description' => 'An expired campaign useful for schedule filtering.', 'cta' => 'Shop body mists', 'url' => '/categories/body-mists', 'priority' => 150, 'active' => true, 'starts' => now()->subMonths(4), 'ends' => now()->subDay()],
            ['storefront' => 'fragrances', 'name' => 'Unpublished Creative Draft', 'eyebrow' => 'Draft', 'title' => 'A campaign in progress.', 'description' => 'An inactive campaign for admin publishing workflows.', 'cta' => 'Explore', 'url' => '/categories/fragrances', 'priority' => 300, 'active' => false, 'starts' => null, 'ends' => null],
            ['storefront' => 'skincare', 'name' => 'The Skin Barrier Edit', 'eyebrow' => 'Comfort-first skincare', 'title' => 'Support your barrier.', 'description' => 'Hydrating skincare for daily routines.', 'cta' => 'Shop moisturizers', 'url' => '/categories/moisturizers', 'priority' => 100, 'active' => true, 'starts' => null, 'ends' => null],
            ['storefront' => 'makeup', 'name' => 'The Colour Story', 'eyebrow' => 'Makeup for every mood', 'title' => 'Turn up the colour.', 'description' => 'A campaign for lips, cheeks, and eyes.', 'cta' => 'Shop makeup', 'url' => '/categories/makeup', 'priority' => 100, 'active' => true, 'starts' => null, 'ends' => null],
        ];

        foreach ($campaigns as $campaign) {
            StorefrontCampaign::updateOrCreate(
                ['storefront_slug' => $campaign['storefront'], 'internal_name' => $campaign['name']],
                [
                    'eyebrow' => $campaign['eyebrow'],
                    'title' => $campaign['title'],
                    'description' => $campaign['description'],
                    'cta_label' => $campaign['cta'],
                    'cta_url' => $campaign['url'],
                    'priority' => $campaign['priority'],
                    'is_active' => $campaign['active'],
                    'starts_at' => $campaign['starts'],
                    'ends_at' => $campaign['ends'],
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]
            );
        }

        $sections = [
            ['type' => HomepageSectionType::RandomCategories, 'title' => 'Explore more', 'config' => ['limit' => 4, 'require_products' => true]],
            ['type' => HomepageSectionType::FeaturedProducts, 'title' => 'Currently coveted', 'config' => ['limit' => 8, 'sort' => 'sort_order', 'direction' => 'asc']],
            ['type' => HomepageSectionType::CollectionProducts, 'title' => 'The Midnight Edit', 'config' => ['collection_slug' => $collections['midnight-edit']->slug, 'limit' => 8]],
            ['type' => HomepageSectionType::CategoryProducts, 'title' => 'Perfume oils', 'config' => ['category_slug' => 'perfume-oils', 'limit' => 8, 'sort' => 'created_at', 'direction' => 'desc']],
            ['type' => HomepageSectionType::SaleProducts, 'title' => 'Now on offer', 'config' => ['limit' => 8]],
            ['type' => HomepageSectionType::ManualProducts, 'title' => 'The scent wardrobe', 'config' => ['limit' => 4]],
            ['type' => HomepageSectionType::NewestProducts, 'title' => 'Just landed', 'config' => ['limit' => 8]],
        ];

        foreach ($sections as $order => $definition) {
            $section = StorefrontHomepageSection::updateOrCreate(
                ['storefront_slug' => 'fragrances', 'type' => $definition['type']->value, 'title' => $definition['title']],
                [
                    'subtitle' => null,
                    'config' => $definition['config'],
                    'display_order' => $order + 1,
                    'is_active' => true,
                    'starts_at' => null,
                    'ends_at' => null,
                    'created_by' => $userId,
                    'updated_by' => $userId,
                ]
            );

            if ($definition['type'] === HomepageSectionType::ManualProducts) {
                $manualSlugs = ['after-dark-eau-de-parfum', 'midnight-amber-perfume-oil', 'white-bloom-eau-de-parfum', 'velvet-oud-perfume-oil'];
                $section->products()->sync(collect($manualSlugs)->mapWithKeys(
                    fn (string $slug, int $index) => [$products[$slug]->id => ['display_order' => $index + 1]]
                )->all());
                $section->touch();
            } else {
                $section->products()->detach();
            }
        }
    }
    private function syncProductCategory(Product $product, string $categoryId, int $sequence): void
    {
        $product->categories()->sync([
            $categoryId => [
                'is_primary' => true,
                'sequence' => $sequence,
            ],
        ]);
    }
}











