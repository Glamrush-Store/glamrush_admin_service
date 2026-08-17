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
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory()->create([
            'name' => 'Demi User',
            'email' => 'demi@example.com',
        ]);

        User::factory(5)->create();

        // Permissions and cohesive application demo data
        $this->call(PermissionsSeeder::class);
        $this->call(AppDataSeeder::class);
        $this->call(ContentManagementSeeder::class);
        $this->call(SiteSettingSeeder::class);

        // Shipping data
        $this->call(ShippingMethodSeeder::class);
        $this->call(ShippingZoneSeeder::class);
        $this->call(ShippingRateSeeder::class);
        $this->call(ShipmentSeeder::class);
        $this->call(PaymentMethodSeeder::class);
    }
}
