<?php

namespace App\Http\Requests\Shipping;

use Illuminate\Foundation\Http\FormRequest;

class CreateShipmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'order_id' => ['required', 'string'],
            'shipping_method_id' => ['required', 'string', 'exists:shipping_methods,id'],
            'shipping_zone_id' => ['required', 'string', 'exists:shipping_zones,id'],
            'shipping_amount' => ['required', 'numeric', 'min:0'],
            'status' => ['sometimes', 'string', 'in:pending,ready,shipped,delivered,failed'],
            'tracking_number' => ['nullable', 'string', 'max:255'],
            'carrier' => ['nullable', 'string', 'max:255'],
            'shipped_at' => ['nullable', 'date'],
            'delivered_at' => ['nullable', 'date'],
        ];
    }
}
