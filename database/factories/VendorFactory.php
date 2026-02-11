<?php

namespace Database\Factories;

use App\Models\Vendor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Vendor>
 */
class VendorFactory extends Factory
{
    protected $model = Vendor::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'business_name' => $this->faker->company(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),

            'password' => Hash::make('password'), // predictable for tests
            'email_verified_at' => now(),

            'code' => strtoupper(Str::random(8)), // public vendor code
            'is_active' => true,

            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => $this->faker->secondaryAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country' => $this->faker->countryCode(),

            'last_stock_sync_at' => null,
        ];
    }

    /**
     * Inactive vendor
     */
    public function inactive(): self
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    /**
     * Vendor with stale stock (offline scenario)
     */
    public function staleStock(int $hours = 24): self
    {
        return $this->state(fn () => [
            'last_stock_sync_at' => now()->subHours($hours),
        ]);
    }

    /**
     * Vendor without verified email
     */
    public function unverified(): self
    {
        return $this->state(fn () => [
            'email_verified_at' => null,
        ]);
    }
}
