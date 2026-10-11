<?php

namespace App\Domain\Shipping\ShippingZone\UseCases;

use App\Domain\Shipping\ShippingZone\Actions\SyncShippingZoneLocationsAction;
use App\Models\ShippingZone;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateShippingZoneUseCase
{
    public function __construct(private SyncShippingZoneLocationsAction $syncLocations) {}

    public function run(ShippingZone $zone, array $data, bool $syncLocations = false): ShippingZone
    {
        return DB::transaction(function () use ($zone, $data, $syncLocations): ShippingZone {
            $zone->update(Arr::except($data, ['state', 'city', 'states', 'cities']));

            if ($syncLocations) {
                $this->syncLocations->run(
                    $zone,
                    $data['country'] ?? $zone->country,
                    $data['states'] ?? [],
                    $data['cities'] ?? [],
                );
            }

            return $zone->fresh()->load(['locations', 'rates.method']);
        });
    }
}
