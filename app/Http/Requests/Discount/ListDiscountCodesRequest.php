<?php

namespace App\Http\Requests\Discount;

use App\Domain\Discount\Enums\DiscountType;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListDiscountCodesRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'search' => ['sometimes', 'string', 'max:255'], 'type' => ['sometimes', Rule::enum(DiscountType::class)],
            'state' => ['sometimes', Rule::in(['draft', 'scheduled', 'active', 'expired', 'inactive'])],
            'storefront_id' => ['sometimes', 'string', 'exists:categories,id'], 'is_active' => ['sometimes', 'boolean'],
            'starts_at_from' => ['sometimes', 'date'], 'starts_at_to' => ['sometimes', 'date'],
            'ends_at_from' => ['sometimes', 'date'], 'ends_at_to' => ['sometimes', 'date'],
            'sort' => ['sometimes', Rule::in(['code', 'name', 'type', 'value', 'starts_at', 'ends_at', 'is_active', 'created_at', 'updated_at'])],
            'direction' => ['sometimes', Rule::in(['asc', 'desc'])], 'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ];
    }
}
