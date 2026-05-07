<?php

namespace Database\Seeders;

use App\Models\Shipment;
use App\Models\ShippingMethod;
use App\Models\ShippingZone;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ShipmentSeeder extends Seeder
{
    public function run(): void
    {
        $standard = ShippingMethod::where('code', 'standard')->first();
        $express  = ShippingMethod::where('code', 'express')->first();
        $lagos    = ShippingZone::where('name', 'Lagos')->first();
        $abuja    = ShippingZone::where('name', 'Abuja')->first();

        if (! $standard || ! $lagos) {
            return;
        }

        $shipments = [
            [
                'order_id'           => Str::ulid(),
                'shipping_method_id' => $standard->id,
                'shipping_zone_id'   => $lagos->id,
                'shipping_amount'    => 1500,
                'status'             => 'delivered',
                'tracking_number'    => 'GR-20260001',
                'carrier'            => 'GIG Logistics',
                'shipped_at'         => now()->subDays(5),
                'delivered_at'       => now()->subDays(2),
            ],
            [
                'order_id'           => Str::ulid(),
                'shipping_method_id' => $express?->id ?? $standard->id,
                'shipping_zone_id'   => $abuja?->id ?? $lagos->id,
                'shipping_amount'    => 4000,
                'status'             => 'shipped',
                'tracking_number'    => 'GR-20260002',
                'carrier'            => 'DHL',
                'shipped_at'         => now()->subDay(),
                'delivered_at'       => null,
            ],
            [
                'order_id'           => Str::ulid(),
                'shipping_method_id' => $standard->id,
                'shipping_zone_id'   => $lagos->id,
                'shipping_amount'    => 0,
                'status'             => 'pending',
                'tracking_number'    => null,
                'carrier'            => null,
                'shipped_at'         => null,
                'delivered_at'       => null,
            ],
        ];

        foreach ($shipments as $data) {
            Shipment::create($data);
        }
    }
}
