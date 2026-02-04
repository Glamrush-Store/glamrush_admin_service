<?php

namespace Database\Factories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) Str::ulid(),
            'sku' => 'TEST-SKU-'.md5(time()).'-'.Str::random(),
            'price' => $this->faker->randomElement(
                [10000, 20000, 30000, 40000, 50000, 60000, 1000000, 150000, 200000]
            ),
            'sale_price' => $this->faker->randomElement(
                [10000, 20000, 30000, 40000, 50000, 60000, 1000000, 150000, 200000]
            ),
            'sale_starts_at' => now()->addMonth(),
            'sale_ends_at' => now()->addMonths(2),
            'manage_stock' => $this->faker->boolean(),
            'in_stock' => $this->faker->boolean(),
            'stock_quantity' => $this->faker->randomNumber(),
        ];
    }

    // public function configure()
    // {
    //     return $this->afterCreating(function (ProductVariant $productVariant) {
    //         $productVariant->addMedia($this->faker->productImage())->toMediaCollection(
    //             'product_images',
    //             'public'
    //         );
    //     });
    // }

    /**
     * Simple product
     */
    public function simple(): static
    {
        return $this->state(fn () => [
            'is_default' => true,
            'attributes' => [],
        ]);
    }

    /**
     * Variable product variant
     */
    public function variable(): static
    {
        return $this->state(function () {
            return [
                'attributes' => [
                    'color' => fake()->randomElement(['red', 'blue', 'black']),
                    'size' => fake()->randomElement(['S', 'M', 'L']),
                ],
            ];
        });
    }

    /**
     * Default variant (important)
     */
    public function default(): static
    {
        return $this->state(fn () => [
            'is_default' => true,
        ]);
    }
}
