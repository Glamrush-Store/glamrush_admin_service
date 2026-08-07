<?php

namespace App\Http\Requests\Shipping;

use Illuminate\Foundation\Http\FormRequest;

class CreateShippingRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_zone_id' => ['required', 'string', 'exists:shipping_zones,id'],
            'shipping_method_id' => ['required', 'string', 'exists:shipping_methods,id'],
            'rate_type' => ['required', 'string', 'in:flat,order_total,weight'],
            'amount' => ['required', 'numeric', 'min:0'],
            'free_over_amount' => ['nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_order_amount' => ['nullable', 'numeric', 'min:0'],
            'estimated_days_min' => ['nullable', 'integer', 'min:0'],
            'estimated_days_max' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
