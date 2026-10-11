<?php

namespace App\Http\Requests\Storefront;

use App\Domain\Storefront\Enums\HomepageSectionType;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class StorefrontHomepageSectionRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        $type = $this->input('type');

        if (! is_string($type)) {
            return;
        }

        $aliases = [
            'product_grid' => HomepageSectionType::ManualProducts->value,
            'category_grid' => HomepageSectionType::RandomCategories->value,
            'collection' => HomepageSectionType::CollectionProducts->value,
        ];

        if (array_key_exists($type, $aliases)) {
            $this->merge(['type' => $aliases[$type]]);
        }
    }

    public function rules(): array
    {
        $creating = $this->isMethod('post');
        $type = $this->input('type', $this->route('section')?->type?->value);
        $validatingConfig = $creating || $this->has('config') || $this->has('type');
        $splitBanner = $type === HomepageSectionType::SplitImageBanner->value;
        $section = $this->route('section');

        $rules = [
            'type' => [$creating ? 'required' : 'sometimes', Rule::enum(HomepageSectionType::class)],
            'title' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'config' => [$creating || $this->has('type') ? 'present' : 'sometimes', 'array'],
            'config.limit' => ['sometimes', 'integer', 'between:1,50'],
            'left_image' => [Rule::requiredIf($splitBanner && ($creating || ! $section?->hasMedia('left-image'))), 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'right_image' => [Rule::requiredIf($splitBanner && ($creating || ! $section?->hasMedia('right-image'))), 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'mobile_image' => [Rule::requiredIf($splitBanner && ($creating || ! $section?->hasMedia('mobile-image'))), 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
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
        } elseif ($validatingConfig && $splitBanner) {
            $rules['config.items'] = ['required', 'array', 'size:2'];
            $rules['config.items.*.slot'] = ['required', 'string', 'distinct', Rule::in(['left', 'right'])];
            $rules['config.items.*.description'] = ['required', 'string', 'max:1000'];
            $rules['config.items.*.cta_label'] = ['nullable', 'string', 'max:100'];
            $rules['config.items.*.cta_url'] = ['nullable', 'string', 'max:2048', 'regex:/^(\/|https?:\/\/)/i'];
            $rules['config.items.*.sort_order'] = ['required', 'integer', 'min:0', 'distinct'];
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

            if ($type === HomepageSectionType::SplitImageBanner && is_array($config['items'] ?? null)) {
                $slots = collect($config['items'])->pluck('slot')->sort()->values()->all();
                if ($slots !== ['left', 'right']) {
                    $validator->errors()->add('config.items', 'The split image banner must contain one left item and one right item.');
                }

                foreach ($config['items'] as $index => $item) {
                    $hasLabel = filled($item['cta_label'] ?? null);
                    $hasUrl = filled($item['cta_url'] ?? null);
                    if ($hasLabel && ! $hasUrl) {
                        $validator->errors()->add("config.items.{$index}.cta_url", 'A CTA URL is required when a CTA label is provided.');
                    }
                    if ($hasUrl && ! $hasLabel) {
                        $validator->errors()->add("config.items.{$index}.cta_label", 'A CTA label is required when a CTA URL is provided.');
                    }
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
