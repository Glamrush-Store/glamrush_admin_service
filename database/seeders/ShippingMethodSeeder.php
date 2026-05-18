<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use Illuminate\Database\Seeder;

class ShippingMethodSeeder extends Seeder
{
    public function run(): void
    {
        $methods = [
            [
                'name'        => 'Standard Delivery',
                'code'        => 'standard',
                'description' => 'Regular delivery within 3–7 business days.',
                'is_active'   => true,
                'sort_order'  => 1,
            ],
            [
                'name'        => 'Express Delivery',
                'code'        => 'express',
                'description' => 'Fast delivery within 1–2 business days.',
                'is_active'   => true,
                'sort_order'  => 2,
            ],
            [
                'name'        => 'Store Pickup',
                'code'        => 'pickup',
                'description' => 'Collect your order from our nearest store at no extra cost.',
                'is_active'   => true,
                'sort_order'  => 3,
            ],
        ];

        foreach ($methods as $data) {
            ShippingMethod::firstOrCreate(['code' => $data['code']], $data);
        }
    }
}
