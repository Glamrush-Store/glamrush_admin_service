<?php

namespace App\Http\Requests\ContactSubmission;

use App\Domain\Contact\Enums\ContactSubmissionStatus;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListContactSubmissionsRequest extends ApiRequest
{
    public const SORT_FIELDS = [
        'name',
        'email',
        'status',
        'source',
        'resolved_at',
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
            'status' => ['sometimes', 'nullable', Rule::enum(ContactSubmissionStatus::class)],
            'source' => ['sometimes', 'nullable', 'string', 'max:100'],
            'storefront_category_id' => ['sometimes', 'nullable', 'string', 'ulid'],
            'customer_account_id' => ['sometimes', 'nullable', 'integer', 'min:1'],
            'created_from' => ['sometimes', 'nullable', 'date'],
            'created_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:created_from'],
            'resolved_from' => ['sometimes', 'nullable', 'date'],
            'resolved_to' => ['sometimes', 'nullable', 'date', 'after_or_equal:resolved_from'],
            'sort_by' => ['sometimes', 'string', Rule::in(self::SORT_FIELDS)],
            'sort_dir' => ['sometimes', 'string', Rule::in(['asc', 'desc'])],
        ];
    }
}
