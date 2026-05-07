<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class CreateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by middleware / policies
        return true;
    }

    public function rules(): array
    {
        return [
            // Identity
            'name' => ['required', 'string', 'max:255'],
            'business_name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:vendors,email'],
            'phone' => ['nullable', 'string', 'max:50'],

            // Auth
            'password' => ['sometimes', 'string', 'min:8'],

            // Business
            'code' => ['required', 'string', 'max:50', 'unique:vendors,code'],
            'is_active' => ['sometimes', 'boolean'],

            // Address
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:30'],
            'country' => ['nullable', 'string', 'max:255'], // ISO country code
        ];
    }

    /**
     * Clean, explicit payload for the UseCase
     */
    public function payload(): array
    {
        return $this->validated();
    }
}
