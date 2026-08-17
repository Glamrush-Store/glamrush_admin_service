<?php

namespace App\Http\Requests\Content;

use App\Domain\Content\Enums\ContentPageType;
use App\Domain\Content\Services\HtmlSanitizer;
use App\Http\Requests\ApiRequest;
use App\Http\Requests\Content\Concerns\ValidatesStorefrontTargeting;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpsertContentPageRequest extends ApiRequest
{
    use ValidatesStorefrontTargeting;

    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => Str::slug(trim((string) $this->input('slug')))]);
        } elseif ($this->isMethod('post') && $this->filled('title')) {
            $this->merge(['slug' => Str::slug(trim((string) $this->input('title')))]);
        }
    }

    public function rules(): array
    {
        $creating = $this->isMethod('post');
        $id = $this->route('contentPage')?->id;

        return [
            'slug' => [$creating ? 'required' : 'sometimes', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('content_pages', 'slug')->ignore($id)],
            'title' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'navigation_title' => ['nullable', 'string', 'max:100'], 'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => [$creating ? 'required' : 'sometimes', 'string', 'max:1000000', 'not_regex:/data\s*:\s*image\//i'],
            'page_type' => [$creating ? 'required' : 'sometimes', Rule::enum(ContentPageType::class)],
            'settings' => ['nullable', 'array:email,phone,whatsapp,business_hours,address,map_url,social_links'], 'settings.email' => ['nullable', 'email:rfc', 'max:255'],
            'settings.phone' => ['nullable', 'string', 'regex:/^[+0-9() .-]{7,30}$/'], 'settings.whatsapp' => ['nullable', 'string', 'regex:/^[+0-9() .-]{7,30}$/'],
            'settings.business_hours' => ['nullable', 'string', 'max:500'], 'settings.address' => ['nullable', 'string', 'max:1000'],
            'settings.map_url' => ['nullable', 'url:http,https', 'max:2048'], 'settings.social_links' => ['nullable', 'array', 'max:20'], 'settings.social_links.*' => ['array:platform,url'],
            'settings.social_links.*.platform' => ['required', Rule::in(['instagram', 'facebook', 'x', 'tiktok', 'youtube', 'linkedin'])],
            'settings.social_links.*.url' => ['required', 'url:http,https', 'max:2048'],
            'meta_title' => ['nullable', 'string', 'max:255'], 'meta_description' => ['nullable', 'string', 'max:500'],
            'is_published' => ['sometimes', 'boolean'], 'published_at' => ['nullable', 'date'], 'expires_at' => ['nullable', 'date'],
            'applies_to_all_storefronts' => ['sometimes', 'boolean'], 'storefront_ids' => ['sometimes', 'array', 'distinct'],
            'storefront_ids.*' => ['string', Rule::exists('categories', 'id')->where(fn ($q) => $q->whereNull('parent_id')->whereNull('deleted_at')->where('is_active', true))],
            'display_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $existing = $this->route('contentPage');
            $this->validateStorefrontTargeting($validator, $existing);
            $published = $this->input('published_at', $existing?->published_at);
            $expires = $this->input('expires_at', $existing?->expires_at);
            if ($published && $expires && strtotime((string) $expires) <= strtotime((string) $published)) {
                $validator->errors()->add('expires_at', 'The expiration time must be later than the publication time.');
            }
            $type = $this->input('page_type', $existing?->page_type?->value);
            if ($type !== ContentPageType::Contact->value && $this->input('settings', $existing?->settings) !== null) {
                $validator->errors()->add('settings', 'Structured settings are only valid for contact pages.');
            }
            if ($this->has('content') && trim(strip_tags(app(HtmlSanitizer::class)->sanitize((string) $this->input('content')))) === '') {
                $validator->errors()->add('content', 'Content must contain text after unsafe markup is removed.');
            }
        });
    }
}
