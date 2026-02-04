<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = $this->faker->word();

        return [
            'id' => (string) Str::ulid(),
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(2),
            'parent_id' => null,
            'is_active' => true,
            'description' => $this->faker->paragraphs(2, true),
            'meta_title' => $name,
            'meta_description' => $this->faker->paragraphs(4, true),
            'sort_order' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    public function withImage(): static
    {
        return $this->afterCreating(function ($category) {
            $category
                ->addMedia($this->faker->categoryImage($category->name))
                ->toMediaCollection('category_images', 'public');
        });
    }
}
