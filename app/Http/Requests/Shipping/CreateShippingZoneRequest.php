<?php

namespace App\Http\Requests\Shipping;

use App\Domain\Shipping\LocationOptions\Actions\ReadCountryLocationOptionsAction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class CreateShippingZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'country' => ['required', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'states' => ['present', 'array'],
            'states.*' => ['required', 'string', 'max:100', 'distinct:ignore_case'],
            'cities' => ['present', 'array'],
            'cities.*.state' => ['required', 'string', 'max:100'],
            'cities.*.city' => ['required', 'string', 'max:100'],
            'postal_code_pattern' => ['nullable', 'string', 'max:100'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $states = is_array($this->input('states')) ? $this->input('states') : [];
        $cities = is_array($this->input('cities')) ? $this->input('cities') : [];

        if (! $this->exists('states') && ! $this->exists('cities')) {
            if (filled($this->input('city'))) {
                $cities = [[
                    'state' => $this->input('state'),
                    'city' => $this->input('city'),
                ]];
            } elseif (filled($this->input('state'))) {
                $states = [$this->input('state')];
            }
        }

        $this->merge($this->normalizeLocations($states, $cities));
    }

    public function after(): array
    {
        return [fn (Validator $validator) => $this->validateLocationOptions($validator)];
    }

    private function normalizeLocations(array $states, array $cities): array
    {
        return [
            'country' => strtoupper(trim((string) $this->input('country'))),
            'states' => collect($states)->map(fn ($state) => strtoupper(trim((string) $state)))->values()->all(),
            'cities' => collect($cities)->map(fn ($city) => [
                'state' => strtoupper(trim((string) ($city['state'] ?? ''))),
                'city' => trim((string) ($city['city'] ?? '')),
            ])->values()->all(),
        ];
    }

    private function validateLocationOptions(Validator $validator): void
    {
        if ($validator->errors()->has('country')) {
            return;
        }

        $options = app(ReadCountryLocationOptionsAction::class)->countryLocations((string) $this->input('country'));

        if ($options === null) {
            $validator->errors()->add('country', 'The selected country is invalid.');

            return;
        }

        $states = collect($options['states'])->keyBy(fn (array $state) => strtoupper($state['value']));
        $cities = collect($options['cities'])->keyBy(
            fn (array $city) => strtoupper($city['state_value']).'|'.mb_strtolower(trim($city['value']))
        );

        foreach ($this->input('states', []) as $index => $state) {
            if (! $states->has(strtoupper($state))) {
                $validator->errors()->add("states.{$index}", 'The selected state is invalid for this country.');
            }
        }

        foreach ($this->input('cities', []) as $index => $city) {
            $key = strtoupper($city['state'] ?? '').'|'.mb_strtolower(trim($city['city'] ?? ''));
            if (! $cities->has($key)) {
                $validator->errors()->add("cities.{$index}.city", 'The selected city is invalid for this country and state.');
            }
        }
    }
}
