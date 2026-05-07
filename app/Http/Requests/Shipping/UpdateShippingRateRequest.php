<?php

namespace App\Http\Requests\Shipping;

use Illuminate\Foundation\Http\FormRequest;

class UpdateShippingRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'shipping_zone_id' => ['sometimes', 'string', 'exists:shipping_zones,id'],
            'shipping_method_id' => ['sometimes', 'string', 'exists:shipping_methods,id'],
            'rate_type' => ['sometimes', 'string', 'in:flat,order_total,weight'],
            'amount' => ['sometimes', 'numeric', 'min:0'],
            'free_over_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'min_order_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'max_order_amount' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'estimated_days_min' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'estimated_days_max' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
