<?php

namespace App\Domain\Shipping\LocationOptions\Actions;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use RuntimeException;

class ReadCountryLocationOptionsAction
{
    private const CACHE_KEY = 'shipping:location-options:countries-json';

    public function countries(): array
    {
        return $this->sourceCountries()
            ->map(fn (array $country) => [
                'label' => $country['name'] ?? null,
                'code' => $country['code3'] ?? null,
                'value' => $country['code3'] ?? null,
            ])
            ->filter(fn (array $country) => filled($country['label']) && filled($country['code']))
            ->unique('code')
            ->sortBy('label')
            ->values()
            ->all();
    }

    public function countryLocations(string $countryCode): ?array
    {
        $country = $this->findCountry($countryCode);

        if ($country === null) {
            return null;
        }

        $states = collect($country['states'] ?? []);

        return [
            'country' => [
                'label' => $country['name'] ?? null,
                'code' => $country['code3'] ?? null,
                'value' => $country['code3'] ?? null,
            ],
            'states' => $states
                ->map(fn (array $state) => [
                    'label' => $state['name'] ?? null,
                    'value' => isset($state['code']) ? trim((string) $state['code']) : null,
                ])
                ->filter(fn (array $state) => filled($state['label']) && filled($state['value']))
                ->unique('value')
                ->sortBy('label')
                ->values()
                ->all(),
            'cities' => $states
                ->flatMap(fn (array $state) => $this->cityOptionsFromState($state))
                ->unique(fn (array $city) => $city['value'].'|'.$city['state_value'])
                ->sortBy(['label', 'state_label'])
                ->values()
                ->all(),
        ];
    }

    private function cityOptionsFromState(array $state): array
    {
        $subdivisions = $state['subdivision'] ?? [];

        if (is_string($subdivisions) && filled($subdivisions)) {
            $subdivisions = [$subdivisions];
        }

        if (! is_array($subdivisions)) {
            return [];
        }

        return collect($subdivisions)
            ->filter(fn ($subdivision) => is_string($subdivision) && filled($subdivision))
            ->map(fn (string $subdivision) => [
                'label' => $subdivision,
                'value' => $subdivision,
                'state_label' => $state['name'] ?? null,
                'state_value' => isset($state['code']) ? trim((string) $state['code']) : null,
            ])
            ->values()
            ->all();
    }

    private function findCountry(string $countryCode): ?array
    {
        $countryCode = strtoupper(trim($countryCode));

        return $this->sourceCountries()
            ->first(fn (array $country) => strtoupper((string) ($country['code3'] ?? '')) === $countryCode);
    }

    private function sourceCountries(): Collection
    {
        return collect(Cache::rememberForever(self::CACHE_KEY, function () {
            $path = resource_path('files/countries.json');

            if (! File::exists($path)) {
                throw new RuntimeException('Countries location file was not found.');
            }

            $contents = File::get($path);
            $countries = $this->decodeCountries($contents);

            if ($countries === null) {
                $contents = mb_convert_encoding($contents, 'UTF-8', 'Windows-1252,ISO-8859-1,UTF-8');
                $countries = $this->decodeCountries($contents);
            }

            if ($countries === null) {
                throw new RuntimeException('Countries location file is not valid JSON.');
            }

            return $countries;
        }));
    }

    private function decodeCountries(string $contents): ?array
    {
        $countries = json_decode($contents, true);

        if (is_array($countries)) {
            return $this->dedupeCountries($countries);
        }

        return $this->decodeCountryObjects($contents);
    }

    private function decodeCountryObjects(string $contents): ?array
    {
        $countries = [];
        $stack = [];
        $inString = false;
        $escaped = false;
        $length = strlen($contents);

        for ($i = 0; $i < $length; $i++) {
            $char = $contents[$i];

            if ($inString) {
                if ($escaped) {
                    $escaped = false;
                    continue;
                }

                if ($char === '\\') {
                    $escaped = true;
                    continue;
                }

                if ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;
                continue;
            }

            if ($char === '{') {
                $stack[] = $i;
                continue;
            }

            if ($char === '}' && $stack !== []) {
                $start = array_pop($stack);
                $object = substr($contents, $start, $i - $start + 1);
                $decoded = json_decode($object, true);

                if (is_array($decoded) && isset($decoded['code3'], $decoded['name'])) {
                    $countries[] = $decoded;
                }
            }
        }

        return $countries === [] ? null : $this->dedupeCountries($countries);
    }

    private function dedupeCountries(array $countries): array
    {
        return collect($countries)
            ->filter(fn ($country) => is_array($country) && isset($country['code3'], $country['name']))
            ->sortByDesc(fn (array $country) => count($country['states'] ?? []))
            ->unique(fn (array $country) => strtoupper((string) $country['code3']))
            ->values()
            ->all();
    }
}
