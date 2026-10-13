<?php

namespace App\Domain\Shipping\ShippingZone\UseCases;

use App\Domain\Shipping\ShippingZone\Actions\SyncShippingZoneLocationsAction;
use App\Models\ShippingZone;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class CreateShippingZoneUseCase
{
    public function __construct(private SyncShippingZoneLocationsAction $syncLocations) {}

    public function run(array $data): ShippingZone
    {
        return DB::transaction(function () use ($data): ShippingZone {
            $zone = ShippingZone::create(Arr::except($data, ['state', 'city', 'states', 'cities']));
            $this->syncLocations->run(
                $zone,
                $data['country'],
                $data['states'] ?? [],
                $data['cities'] ?? [],
            );

            return $zone->fresh()->load(['locations', 'rates.method']);
        });
    }
}
