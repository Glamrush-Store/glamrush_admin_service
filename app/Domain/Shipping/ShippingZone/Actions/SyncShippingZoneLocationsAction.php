<?php

namespace App\Domain\Shipping\ShippingZone\Actions;

use App\Exceptions\BusinessException;
use App\Models\ShippingZone;
use App\Models\ShippingZoneLocation;
use Illuminate\Support\Collection;

class SyncShippingZoneLocationsAction
{
    private const GROUPED_LOCATION_MARKER = '__grouped__';

    public function run(ShippingZone $zone, string $country, array $states, array $cities): void
    {
        $locations = $this->locations($country, $states, $cities);
        $matchKeys = $locations->pluck('match_key')->all();

        $conflict = ShippingZoneLocation::query()
            ->whereIn('match_key', $matchKeys)
            ->where('shipping_zone_id', '!=', $zone->id)
            ->with('zone:id,name')
            ->first();

        if ($conflict !== null) {
            throw new BusinessException(
                "The location is already assigned to shipping zone '{$conflict->zone->name}'.",
                ['locations' => ['Each country, state, or city can only belong to one shipping zone at that scope.']],
                409,
            );
        }

        $zone->locations()->delete();
        $zone->locations()->createMany($locations->all());

        [$legacyState, $legacyCity] = $this->legacyLocation($locations);
        $zone->forceFill([
            'country' => strtoupper(trim($country)),
            'state' => $legacyState,
            'city' => $legacyCity,
        ])->save();
    }

    private function locations(string $country, array $states, array $cities): Collection
    {
        $country = strtoupper(trim($country));
        $locations = collect();

        foreach ($states as $state) {
            $state = strtoupper(trim((string) $state));
            $locations->push([
                'country' => $country,
                'scope' => 'state',
                'state' => $state,
                'city' => null,
                'match_key' => implode('|', [$country, 'state', $state]),
            ]);
        }

        foreach ($cities as $city) {
            $state = strtoupper(trim((string) $city['state']));
            $name = trim((string) $city['city']);
            $locations->push([
                'country' => $country,
                'scope' => 'city',
                'state' => $state,
                'city' => $name,
                'match_key' => implode('|', [$country, 'city', $state, mb_strtolower($name)]),
            ]);
        }

        if ($locations->isEmpty()) {
            $locations->push([
                'country' => $country,
                'scope' => 'country',
                'state' => null,
                'city' => null,
                'match_key' => implode('|', [$country, 'country']),
            ]);
        }

        return $locations->unique('match_key')->values();
    }

    private function legacyLocation(Collection $locations): array
    {
        if ($locations->count() !== 1) {
            return [self::GROUPED_LOCATION_MARKER, self::GROUPED_LOCATION_MARKER];
        }

        $location = $locations->first();

        return match ($location['scope']) {
            'city' => [$location['state'], $location['city']],
            'state' => [$location['state'], null],
            default => [null, null],
        };
    }
}
