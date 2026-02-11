<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SkuAttributeCode>
 */
class SkuAttributeCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected static array $pool = [];

    public function definition(): array
    {
        if (empty(self::$pool)) {
            self::$pool = collect([
                'color' => [
                    'Black' => 'BLK',
                    'White' => 'WHT',
                    'Red' => 'RED',
                    'Blue' => 'BLU',
                ],
                'size' => [
                    'Small' => 'S',
                    'Medium' => 'M',
                    'Large' => 'L',
                    'XL' => 'XL',
                ],
                'fragrance' => [
                    'Lavender' => 'LVND',
                    'Vanilla' => 'VNLA',
                    'Rose' => 'ROSE',
                    'Citrus' => 'CTRS',
                ],
            ])
                ->flatMap(fn ($values, $type) => collect($values)->map(fn ($code, $value) => [
                    'type' => $type,
                    'value' => $value,
                    'code' => $code,
                ])
                )
                ->shuffle()
                ->values()
                ->all();
        }

        return array_merge(array_shift(self::$pool), [
            'is_active' => true,
        ]);
    }
}
