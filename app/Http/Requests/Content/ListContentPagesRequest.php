<?php

namespace App\Http\Requests\Content;

use App\Domain\Content\Enums\ContentPageType;
use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListContentPagesRequest extends ApiRequest
{
    public function rules(): array
    {
        return ['search' => ['sometimes', 'string', 'max:255'], 'page_type' => ['sometimes', Rule::enum(ContentPageType::class)], 'state' => ['sometimes', Rule::in(['draft', 'scheduled', 'published', 'unpublished', 'expired'])], 'storefront_id' => ['sometimes', 'string', 'exists:categories,id'], 'is_published' => ['sometimes', 'boolean'], 'published_from' => ['sometimes', 'date'], 'published_to' => ['sometimes', 'date'], 'expires_from' => ['sometimes', 'date'], 'expires_to' => ['sometimes', 'date'], 'sort' => ['sometimes', Rule::in(['title', 'slug', 'page_type', 'display_order', 'is_published', 'published_at', 'expires_at', 'created_at', 'updated_at'])], 'direction' => ['sometimes', Rule::in(['asc', 'desc'])], 'per_page' => ['sometimes', 'integer', 'between:1,100']];
    }
}
