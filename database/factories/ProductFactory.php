<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    protected static array $categoryIds;

    protected static array $brandIds;

    protected static array $vendorIds;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Cache IDs once (important for performance)
        static::$categoryIds ??= Category::pluck('id')->all();
        static::$brandIds ??= Brand::pluck('id')->all();
        static::$vendorIds ??= Vendor::pluck('id')->all();

        return [
            'id' => (string) Str::ulid(),
            'name' => $this->getProductName(),
            'vendor_id' => fake()->randomElement(static::$vendorIds),
            'slug' => Str::slug($this->getProductName(), '-'),
            'category_id' => fake()->randomElement(static::$categoryIds),
            'brand_id' => fake()->randomElement(static::$brandIds),
            'short_description' => $this->faker->text(),
            'description' => $this->faker->paragraphs(4, true),
            'status' => $this->faker->randomElement(['draft', 'published', 'archived']),
            'published_at' => $this->faker->date(),
            'meta_title' => $this->faker->text(),
            'type' => $this->faker->randomElement(['simple', 'variable']),
            'meta_description' => $this->faker->text(),
            'meta_keywords' => $this->faker->words(9, true),
            'is_featured' => $this->faker->boolean(),
            'price' => $this->faker->randomFloat(2, 0, 10000),
            'sale_price' => $this->faker->randomFloat(2, 0, 10000),
            'sale_starts_at' => $this->faker->date(),
            'sale_ends_at' => $this->faker->date(),
            'manage_stock' => $this->faker->boolean(),
            'in_stock' => $this->faker->boolean(),
            'sequence' => $this->faker->unique()->numberBetween(1, 999999),
            'stock_quantity' => $this->faker->randomNumber(),
            'sort_order' => $this->faker->randomNumber(),
            'views_count' => $this->faker->randomNumber(),
            'sales_count' => $this->faker->randomNumber(),
        ];
    }

    private function getProductName(): string
    {
        return $this->faker->words(3, true);
    }

    public function withImage(): static
    {
        return $this->afterCreating(function (Product $product) {
            $product
                ->addMedia($this->faker->productImage())
                ->toMediaCollection('product_images', 'public');
        });
    }

    /**
     * Simple product (exactly ONE variant)
     */
    public function simple(): static
    {
        return $this->afterCreating(function (Product $product) {
            ProductVariant::factory()
                ->for($product)
                ->simple()
                ->create();
        });
    }

    /**
     * Variable product (multiple variants)
     */
    public function variable(int $variants = 4): static
    {
        return $this->afterCreating(function (Product $product) use ($variants) {
            ProductVariant::factory()
                ->count($variants)
                ->for($product)
                ->variable()
                ->create();

            // Mark first variant as default
            $product->variants()
                ->orderBy('sort_order')
                ->first()
                ?->update(['is_default' => true]);

            $product->update(['type' => 'variable']);
        });
    }
}
