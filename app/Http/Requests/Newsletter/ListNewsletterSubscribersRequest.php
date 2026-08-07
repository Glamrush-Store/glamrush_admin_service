<?php

namespace App\Http\Requests\Newsletter;

use App\Domain\Newsletter\Enums\NewsletterSubscriberStatus;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListNewsletterSubscribersRequest extends ApiRequest
{
    public const SORT_FIELDS = [
        'email',
        'status',
        'source',
        'confirmed_at',
        'unsubscribed_at',
        'created_at',
        'updated_at',
    ];

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'search' => ['sometimes', 'nullable', 'string', 'max:255'],
            'status' => ['sometimes', 'nullable', Rule::enum(NewsletterSubscriberStatus::class)],
            'source' => ['sometimes', 'nullable', 'string', 'max:100'],
            'confirmed_from' => ['sometimes', 'nullable', 'date'],
            'confirmed_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:confirmed_from'],
            'created_from' => ['sometimes', 'nullable', 'date'],
            'created_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:created_from'],
            'sort_by' => ['sometimes', 'string', Rule::in(self::SORT_FIELDS)],
            'sort_dir' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
