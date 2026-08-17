<?php

namespace App\Http\Requests\Content;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ReorderContentRequest extends ApiRequest
{
    public function rules(): array
    {
        $table = $this->routeIs('faq-categories.reorder') ? 'faq_categories' : 'faqs';

        return ['ids' => ['required', 'array', 'min:1', 'max:500'], 'ids.*' => ['required', 'string', 'distinct', Rule::exists($table, 'id')->whereNull('deleted_at')]];
    }
}
