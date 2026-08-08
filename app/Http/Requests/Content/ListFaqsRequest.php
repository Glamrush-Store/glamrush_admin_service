<?php

namespace App\Http\Requests\Content;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListFaqsRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['search' => ['sometimes', 'string', 'max:255'], 'faq_category_id' => ['sometimes', 'string', 'exists:faq_categories,id'], 'state' => ['sometimes', Rule::in(['draft', 'scheduled', 'published', 'unpublished', 'expired'])], 'storefront_id' => ['sometimes', 'string', 'exists:categories,id'], 'is_published' => ['sometimes', 'boolean'], 'sort' => ['sometimes', Rule::in(['question', 'display_order', 'is_published', 'published_at', 'expires_at', 'created_at', 'updated_at'])], 'direction' => ['sometimes', Rule::in(['asc', 'desc'])], 'per_page' => ['sometimes', 'integer', 'between:1,100']];
    }
}
