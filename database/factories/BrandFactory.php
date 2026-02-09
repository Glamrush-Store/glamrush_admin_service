<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Brand>
 */
class BrandFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->words(2, true);

        return [
            'id' => (string) Str::ulid(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(2),
            'code' => strtoupper(Str::random(4)),
            'description' => $this->faker->paragraphs(2, true),
            'meta_title' => $name,
            'meta_description' => $this->faker->paragraphs(4, true),
            'is_active' => true,
        ];
    }

    public function withImage(): static
    {
        return $this->afterCreating(function ($brand) {
            $brand
                ->addMedia($this->faker->brandImage($brand->name))
                ->toMediaCollection('brand_logos', 'public');
        });
    }
}
