<?php

namespace Database\Seeders;

use App\Domain\Shipping\ShippingZone\Actions\SyncShippingZoneLocationsAction;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingZoneSeeder extends Seeder
{
    public function run(SyncShippingZoneLocationsAction $syncLocations): void
    {
        $zones = [
            [
                'name' => 'Lagos',
                'country' => 'NGA',
                'states' => ['LA'],
                'cities' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Abuja',
                'country' => 'NGA',
                'states' => ['FC'],
                'cities' => [],
                'is_active' => true,
            ],
            [
                'name' => 'Port Harcourt',
                'country' => 'NGA',
                'states' => [],
                'cities' => [['state' => 'RI', 'city' => 'Port Harcourt']],
                'is_active' => true,
            ],
            [
                'name' => 'Nationwide',
                'country' => 'NGA',
                'states' => [],
                'cities' => [],
                'is_active' => true,
            ],
        ];

        foreach ($zones as $data) {
            $zone = ShippingZone::updateOrCreate(
                ['name' => $data['name']],
                [
                    'country' => $data['country'],
                    'is_active' => $data['is_active'],
                ],
            );

            $syncLocations->run($zone, $data['country'], $data['states'], $data['cities']);
        }
    }
}
