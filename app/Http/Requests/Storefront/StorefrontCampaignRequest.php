<?php

namespace App\Http\Requests\Storefront;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class StorefrontCampaignRequest extends ApiRequest
{
    public function rules(): array
    {
        $creating = $this->isMethod('post');

        return [
            'internal_name' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'eyebrow' => ['nullable', 'string', 'max:255'],
            'title' => [$creating ? 'required' : 'sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'desktop_image' => ['sometimes', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'mobile_image' => ['sometimes', 'image', 'mimes:jpeg,jpg,png,webp', 'max:10240'],
            'cta_label' => ['nullable', 'string', 'max:100'],
            'cta_url' => ['nullable', 'string', 'max:2048', 'regex:/^(\/|https?:\/\/)/i'],
            'priority' => ['sometimes', 'integer', 'between:-100000,100000'],
            'is_active' => ['sometimes', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', Rule::when($this->filled('starts_at'), ['after:starts_at'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $start = $this->input('starts_at', $this->route('campaign')?->starts_at);
            $end = $this->input('ends_at', $this->route('campaign')?->ends_at);
            if ($start && $end && strtotime((string) $end) <= strtotime((string) $start)) {
                $validator->errors()->add('ends_at', 'The end time must be later than the start time.');
            }
        });
    }
}
