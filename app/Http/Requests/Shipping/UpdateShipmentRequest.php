<?php

namespace App\Http\Requests\Shipping;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_method_id' => ['sometimes', 'string', 'exists:shipping_methods,id'],
            'shipping_zone_id' => ['sometimes', 'string', 'exists:shipping_zones,id'],
            'shipping_amount' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:pending,ready,shipped,delivered,failed'],
            'tracking_number' => ['sometimes', 'nullable', 'string', 'max:255'],
            'carrier' => ['sometimes', 'nullable', 'string', 'max:255'],
            'shipped_at' => ['sometimes', 'nullable', 'date'],
            'delivered_at' => ['sometimes', 'nullable', 'date'],
        ];
    }
}
