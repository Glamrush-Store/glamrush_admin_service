<?php

namespace Database\Seeders;

use App\Models\ShippingMethod;
use App\Models\ShippingRate;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;

class ShippingRateSeeder extends Seeder
{
    public function run(): void
    {
        $standard = ShippingMethod::where('code', 'standard')->first();
        $express  = ShippingMethod::where('code', 'express')->first();
        $pickup   = ShippingMethod::where('code', 'pickup')->first();

        $lagos    = ShippingZone::where('name', 'Lagos')->first();
        $abuja    = ShippingZone::where('name', 'Abuja')->first();
        $ph       = ShippingZone::where('name', 'Port Harcourt')->first();
        $national = ShippingZone::where('name', 'Nationwide')->first();

        $rates = [
            // Lagos
            ['zone' => $lagos,    'method' => $standard, 'rate_type' => 'flat', 'amount' => 1500, 'free_over_amount' => 20000, 'estimated_days_min' => 2, 'estimated_days_max' => 4],
            ['zone' => $lagos,    'method' => $express,  'rate_type' => 'flat', 'amount' => 3000, 'free_over_amount' => 50000, 'estimated_days_min' => 1, 'estimated_days_max' => 1],
            ['zone' => $lagos,    'method' => $pickup,   'rate_type' => 'flat', 'amount' => 0,    'free_over_amount' => null,  'estimated_days_min' => 1, 'estimated_days_max' => 3],

            // Abuja
            ['zone' => $abuja,    'method' => $standard, 'rate_type' => 'flat', 'amount' => 2000, 'free_over_amount' => 25000, 'estimated_days_min' => 3, 'estimated_days_max' => 5],
            ['zone' => $abuja,    'method' => $express,  'rate_type' => 'flat', 'amount' => 4000, 'free_over_amount' => 50000, 'estimated_days_min' => 1, 'estimated_days_max' => 2],
            ['zone' => $abuja,    'method' => $pickup,   'rate_type' => 'flat', 'amount' => 0,    'free_over_amount' => null,  'estimated_days_min' => 1, 'estimated_days_max' => 3],

            // Port Harcourt
            ['zone' => $ph,       'method' => $standard, 'rate_type' => 'flat', 'amount' => 2000, 'free_over_amount' => 25000, 'estimated_days_min' => 3, 'estimated_days_max' => 5],
            ['zone' => $ph,       'method' => $express,  'rate_type' => 'flat', 'amount' => 4500, 'free_over_amount' => 50000, 'estimated_days_min' => 1, 'estimated_days_max' => 2],

            // Nationwide
            ['zone' => $national, 'method' => $standard, 'rate_type' => 'flat', 'amount' => 2500, 'free_over_amount' => 30000, 'estimated_days_min' => 5, 'estimated_days_max' => 7],
            ['zone' => $national, 'method' => $express,  'rate_type' => 'flat', 'amount' => 5000, 'free_over_amount' => 50000, 'estimated_days_min' => 2, 'estimated_days_max' => 3],
        ];

        foreach ($rates as $rate) {
            $zone   = $rate['zone'];
            $method = $rate['method'];

            if (! $zone || ! $method) {
                continue;
            }

            ShippingRate::firstOrCreate(
                [
                    'shipping_zone_id'   => $zone->id,
                    'shipping_method_id' => $method->id,
                ],
                [
                    'rate_type'           => $rate['rate_type'],
                    'amount'              => $rate['amount'],
                    'free_over_amount'    => $rate['free_over_amount'],
                    'estimated_days_min'  => $rate['estimated_days_min'],
                    'estimated_days_max'  => $rate['estimated_days_max'],
                    'is_active'           => true,
                ]
            );
        }
    }
}
