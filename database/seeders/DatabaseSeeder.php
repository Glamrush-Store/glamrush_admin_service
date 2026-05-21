<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Users
        User::factory()->create([
            'name'  => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name'  => 'Demi User',
            'email' => 'demi@example.com',
        ]);

        User::factory(5)->create();

        // Reference / lookup data
        $this->call(PermissionsSeeder::class);
        $this->call(SkuAttributeCodeSeeder::class);
        $this->call(AttributeTypeSeeder::class);

//        // Catalogue data
        $this->call(VendorSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(BrandSeeder::class);
        $this->call(ProductSeeder::class);
        $this->call(CollectionSeeder::class);

        // Shipping data
        $this->call(ShippingMethodSeeder::class);
        $this->call(ShippingZoneSeeder::class);
        $this->call(ShippingRateSeeder::class);
        $this->call(ShipmentSeeder::class);
        $this->call(PaymentMethodSeeder::class);
    }
}
