<?php

namespace Database\Seeders;

use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'name'      => 'Lagos',
                'country'   => 'NG',
                'state'     => 'Lagos',
                'city'      => null,
                'is_active' => true,
            ],
            [
                'name'      => 'Abuja',
                'country'   => 'NG',
                'state'     => 'FCT',
                'city'      => null,
                'is_active' => true,
            ],
            [
                'name'      => 'Port Harcourt',
                'country'   => 'NG',
                'state'     => 'Rivers',
                'city'      => 'Port Harcourt',
                'is_active' => true,
            ],
            [
                'name'      => 'Nationwide',
                'country'   => 'NG',
                'state'     => null,
                'city'      => null,
                'is_active' => true,
            ],
        ];

        foreach ($zones as $data) {
            ShippingZone::firstOrCreate(['name' => $data['name'], 'country' => $data['country']], $data);
        }
    }
}
