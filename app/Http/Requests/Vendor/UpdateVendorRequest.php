<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $vendorId = $this->route('vendorId');

        return [
            // Identity
            'name' => ['sometimes', 'string', 'max:255'],
            'business_name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'email' => [
                'sometimes',
                'email',
                'max:255',
                "unique:vendors,email,{$vendorId},id",
            ],
            'phone' => ['sometimes', 'nullable', 'string', 'max:50'],

            // Auth
            'password' => ['sometimes', 'string', 'min:8'],

            // Business
            'code' => [
                'sometimes',
                'string',
                'max:50',
                "unique:vendors,code,{$vendorId},id",
            ],
            'is_active' => ['sometimes', 'boolean'],

            // Address
            'address_line_1' => ['sometimes', 'nullable', 'string', 'max:255'],
            'address_line_2' => ['sometimes', 'nullable', 'string', 'max:255'],
            'city' => ['sometimes', 'nullable', 'string', 'max:100'],
            'state' => ['sometimes', 'nullable', 'string', 'max:100'],
            'postal_code' => ['sometimes', 'nullable', 'string', 'max:30'],
            'country' => ['sometimes', 'nullable', 'string', 'max:2'],
        ];
    }

    public function payload(): array
    {
        return $this->validated();
    }
}
