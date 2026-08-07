<?php

namespace App\Http\Requests\Storefront;

use App\Domain\Storefront\Enums\HomepageSectionType;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class StorefrontHomepageSectionRequest extends ApiRequest
{
    public function rules(): array
    {
        $creating = $this->isMethod('post');
        $type = $this->input('type', $this->route('section')?->type?->value);
        $validatingConfig = $creating || $this->has('config') || $this->has('type');

        $rules = [
            'type' => [$creating ? 'required' : 'sometimes', Rule::enum(HomepageSectionType::class)],
            'title' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'config' => [$creating || $this->has('type') ? 'present' : 'sometimes', 'array'],
            'config.limit' => ['sometimes', 'integer', 'between:1,50'],
            'display_order' => ['sometimes', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ];

        if ($validatingConfig && $type === HomepageSectionType::CategoryProducts->value) {
            $rules['config.category_slug'] = ['required', 'string', 'max:255', Rule::exists('categories', 'slug')];
            $rules['config.sort'] = ['sometimes', Rule::in(['created_at', 'price', 'sort_order', 'name'])];
            $rules['config.direction'] = ['sometimes', Rule::in(['asc', 'desc'])];
        } elseif ($validatingConfig && $type === HomepageSectionType::CollectionProducts->value) {
            $rules['config.collection_slug'] = ['required', 'string', 'max:255', Rule::exists('collections', 'slug')];
        } elseif ($validatingConfig && $type === HomepageSectionType::FeaturedProducts->value) {
            $rules['config.sort'] = ['sometimes', Rule::in(['created_at', 'price', 'sort_order', 'name'])];
            $rules['config.direction'] = ['sometimes', Rule::in(['asc', 'desc'])];
        } elseif ($validatingConfig && $type === HomepageSectionType::RandomCategories->value) {
            $rules['config.require_products'] = ['sometimes', 'boolean'];
        } elseif ($validatingConfig && $type === HomepageSectionType::ManualProducts->value) {
            $rules['config.product_ids'] = ['required', 'array', 'min:1', 'max:50'];
            $rules['config.product_ids.*'] = ['string', 'distinct', Rule::exists('products', 'id')];
        }

        return $rules;
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $typeValue = $this->input('type', $this->route('section')?->type?->value);
            $type = HomepageSectionType::tryFrom((string) $typeValue);
            $config = $this->input('config');
            if ($type && is_array($config)) {
                $unknown = array_diff(array_keys($config), $type->allowedConfigKeys());
                if ($unknown !== []) {
                    $validator->errors()->add('config', 'Unknown configuration keys: '.implode(', ', $unknown).'.');
                }
            }

            $start = $this->input('starts_at', $this->route('section')?->starts_at);
            $end = $this->input('ends_at', $this->route('section')?->ends_at);
            if ($start && $end && strtotime((string) $end) <= strtotime((string) $start)) {
                $validator->errors()->add('ends_at', 'The end time must be later than the start time.');
            }
        });
    }
}
