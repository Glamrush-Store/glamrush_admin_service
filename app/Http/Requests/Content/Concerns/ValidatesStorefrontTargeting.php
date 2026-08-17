<?php

namespace App\Http\Requests\Content\Concerns;

trait ValidatesStorefrontTargeting
{
    protected function validateStorefrontTargeting($validator, $existing = null): void
    {
        $global = filter_var($this->input('applies_to_all_storefronts', $existing?->applies_to_all_storefronts ?? true), FILTER_VALIDATE_BOOL);
        $ids = $this->has('storefront_ids') ? $this->input('storefront_ids', []) : ($global ? [] : ($existing?->storefronts()->pluck('categories.id')->all() ?? []));
        if (! $global && count($ids) === 0) {
            $validator->errors()->add('storefront_ids', 'Select at least one active root storefront.');
        }
        if ($global && count($ids) > 0) {
            $validator->errors()->add('storefront_ids', 'Global content cannot have storefront associations.');
        }
    }
}
