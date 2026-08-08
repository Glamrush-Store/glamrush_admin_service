<?php

namespace App\Http\Requests\Discount;

use App\Domain\Discount\Enums\DiscountTargetMode;
use App\Domain\Discount\Enums\DiscountTargetType;
use App\Domain\Discount\Enums\DiscountType;
use App\Http\Requests\ApiRequest;
use App\Models\Category;
use Illuminate\Validation\Rule;

class UpsertDiscountCodeRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge(['code' => strtoupper(trim((string) $this->input('code')))]);
        }
        if ($this->has('currency') && $this->input('currency') !== null) {
            $this->merge(['currency' => strtoupper(trim((string) $this->input('currency')))]);
        }
    }

    public function rules(): array
    {
        $creating = $this->isMethod('post');
        $id = $this->route('discountCode')?->id;

        return [
            'code' => [$creating ? 'required' : 'sometimes', 'string', 'max:64', 'regex:/^[A-Z0-9_-]+$/', Rule::unique('discount_codes', 'code')->ignore($id)],
            'name' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'type' => [$creating ? 'required' : 'sometimes', Rule::enum(DiscountType::class)],
            'value' => ['nullable', 'numeric', 'gt:0'],
            'currency' => ['nullable', 'string', 'size:3', 'alpha'],
            'maximum_discount_amount' => ['nullable', 'numeric', 'gt:0'],
            'minimum_subtotal' => ['nullable', 'numeric', 'gte:0'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
            'is_active' => [$creating ? 'required' : 'sometimes', 'boolean'],
            'total_usage_limit' => ['nullable', 'integer', 'min:1'],
            'per_customer_usage_limit' => ['nullable', 'integer', 'min:1'],
            'first_order_only' => [$creating ? 'required' : 'sometimes', 'boolean'],
            'applies_to_sale_items' => [$creating ? 'required' : 'sometimes', 'boolean'],
            'applies_to_all_storefronts' => [$creating ? 'required' : 'sometimes', 'boolean'],
            'storefront_ids' => ['sometimes', 'array', 'distinct'],
            'storefront_ids.*' => ['string', Rule::exists('categories', 'id')->where(fn ($q) => $q->whereNull('parent_id')->whereNull('deleted_at')->where('is_active', true))],
            'targets' => ['sometimes', 'array', 'max:500'],
            'targets.*.target_type' => ['required', Rule::enum(DiscountTargetType::class)],
            'targets.*.target_id' => ['required', 'string'],
            'targets.*.mode' => ['required', Rule::enum(DiscountTargetMode::class)],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $existing = $this->route('discountCode');
            $type = $this->input('type', $existing?->type?->value);
            $value = $this->input('value', $existing?->value);
            $currency = $this->input('currency', $existing?->currency);
            $cap = $this->input('maximum_discount_amount', $existing?->maximum_discount_amount);
            $allStorefronts = filter_var($this->input('applies_to_all_storefronts', $existing?->applies_to_all_storefronts ?? true), FILTER_VALIDATE_BOOL);
            $storefrontIds = $this->has('storefront_ids')
                ? $this->input('storefront_ids', [])
                : ($allStorefronts ? [] : ($existing?->storefronts()->pluck('categories.id')->all() ?? []));

            if ($type === DiscountType::Percentage->value && (! $value || (float) $value > 100)) {
                $validator->errors()->add('value', 'Percentage value must be greater than zero and no more than 100.');
            }
            if ($type === DiscountType::FixedAmount->value && (! $value || ! $currency)) {
                $validator->errors()->add('currency', 'Currency and a positive value are required for fixed-amount discounts.');
            }
            if ($type === DiscountType::FreeShipping->value && $value !== null) {
                $validator->errors()->add('value', 'Free-shipping discounts cannot have a monetary value.');
            }
            if ($type !== DiscountType::FixedAmount->value && $currency !== null) {
                $validator->errors()->add('currency', 'Currency is only valid for fixed-amount discounts.');
            }
            if ($type !== DiscountType::Percentage->value && $cap !== null) {
                $validator->errors()->add('maximum_discount_amount', 'A maximum discount amount is only valid for percentage discounts.');
            }
            $totalLimit = $this->input('total_usage_limit', $existing?->total_usage_limit);
            $customerLimit = $this->input('per_customer_usage_limit', $existing?->per_customer_usage_limit);
            if ($totalLimit && $customerLimit && $customerLimit > $totalLimit) {
                $validator->errors()->add('per_customer_usage_limit', 'The per-customer limit cannot exceed the total usage limit.');
            }
            if (! $allStorefronts && count($storefrontIds) === 0) {
                $validator->errors()->add('storefront_ids', 'Select at least one active root storefront.');
            }
            if ($allStorefronts && count($storefrontIds) > 0) {
                $validator->errors()->add('storefront_ids', 'Global discounts cannot have storefront associations.');
            }

            $start = $this->input('starts_at', $existing?->starts_at);
            $end = $this->input('ends_at', $existing?->ends_at);
            if ($start && $end && strtotime((string) $end) <= strtotime((string) $start)) {
                $validator->errors()->add('ends_at', 'The end time must be later than the start time.');
            }

            $seen = [];
            foreach ($this->input('targets', []) as $i => $target) {
                $typeEnum = DiscountTargetType::tryFrom($target['target_type'] ?? '');
                $targetId = $target['target_id'] ?? null;
                if (! $typeEnum || ! $targetId) {
                    continue;
                }
                $key = $typeEnum->value.':'.$targetId;
                if (isset($seen[$key])) {
                    $validator->errors()->add("targets.$i", 'A target cannot be duplicated or both included and excluded.');
                }
                $seen[$key] = true;
                if (! \DB::table($typeEnum->table())->where('id', $targetId)->exists()) {
                    $validator->errors()->add("targets.$i.target_id", 'The selected catalog target does not exist.');
                }
                if (! $allStorefronts && $typeEnum === DiscountTargetType::Category) {
                    $category = Category::find($targetId);
                    while ($category?->parent_id) {
                        $category = Category::find($category->parent_id);
                    }
                    if ($category && ! in_array($category->id, $storefrontIds, true)) {
                        $validator->errors()->add("targets.$i.target_id", 'The category target is outside the selected storefronts.');
                    }
                }
            }
        });
    }
}
