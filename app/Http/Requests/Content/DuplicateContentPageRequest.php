<?php

namespace App\Http\Requests\Content;

use App\Http\Requests\ApiRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DuplicateContentPageRequest extends ApiRequest
{
    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) {
            $this->merge(['slug' => Str::slug(trim((string) $this->input('slug')))]);
        }
    }

    public function rules(): array
    {
        return ['slug' => ['required', 'string', 'max:160', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('content_pages', 'slug')]];
    }
}
