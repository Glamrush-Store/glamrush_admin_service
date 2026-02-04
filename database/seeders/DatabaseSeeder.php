<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
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

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        User::factory(5)->create();

        Category::factory()
            ->count(10)
            ->create();

        Brand::factory()
            ->count(5)
            ->create();

        Product::factory()
            ->count(50)
            ->simple()
            ->create();

        Product::factory()
            ->count(100)
            ->variable(3)
            ->create();

        $this->call(PermissionsSeeder::class);
    }
}
