<?php

namespace App\Http\Requests\Content;

use App\Domain\Content\Services\HtmlSanitizer;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\Content\Concerns\ValidatesStorefrontTargeting;
use Illuminate\Validation\Rule;

class UpsertFaqRequest extends ApiRequest
{
    use ValidatesStorefrontTargeting;

    public function rules(): array
    {
        $creating = $this->isMethod('post');

        return ['faq_category_id' => [$creating ? 'required' : 'sometimes', Rule::exists('faq_categories', 'id')->where(fn ($q) => $q->whereNull('deleted_at')->where('is_active', true))], 'question' => [$creating ? 'required' : 'sometimes', 'string', 'max:500'], 'answer' => [$creating ? 'required' : 'sometimes', 'string', 'max:1000000', 'not_regex:/data\s*:\s*image\//i'], 'display_order' => ['sometimes', 'integer', 'min:0'], 'is_published' => ['sometimes', 'boolean'], 'published_at' => ['nullable', 'date'], 'expires_at' => ['nullable', 'date'], 'applies_to_all_storefronts' => ['sometimes', 'boolean'], 'storefront_ids' => ['sometimes', 'array', 'distinct'], 'storefront_ids.*' => ['string', Rule::exists('categories', 'id')->where(fn ($q) => $q->whereNull('parent_id')->whereNull('deleted_at')->where('is_active', true))]];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $existing = $this->route('faq');
            $this->validateStorefrontTargeting($validator, $existing);
            $published = $this->input('published_at', $existing?->published_at);
            $expires = $this->input('expires_at', $existing?->expires_at);
            if ($published && $expires && strtotime((string) $expires) <= strtotime((string) $published)) {
                $validator->errors()->add('expires_at', 'The expiration time must be later than the publication time.');
            }
            if ($this->has('answer') && trim(strip_tags(app(HtmlSanitizer::class)->sanitize((string) $this->input('answer')))) === '') {
                $validator->errors()->add('answer', 'Answer must contain text after unsafe markup is removed.');
            }
        });
    }
}
