<?php

namespace App\Http\Requests\ProductImport;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class StoreProductImportRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,txt',
                'max:'.config('product_imports.max_file_size_kb'),
            ],
            'vendor_id' => ['required', 'string', Rule::exists('vendors', 'id')],
            'brand_id' => ['required', 'string', Rule::exists('brands', 'id')],
            'category_ids' => ['required', 'array', 'min:1'],
            'category_ids.*' => ['required', 'string', 'distinct', Rule::exists('categories', 'id')],
            'primary_category_id' => ['required', 'string', Rule::exists('categories', 'id')],
            'category_sequences' => ['sometimes', 'array'],
            'category_sequences.*' => ['integer', 'min:0'],
            'default_status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'image_failure_policy' => ['sometimes', Rule::in(['warn', 'fail_product'])],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! collect($this->input('category_ids', []))->contains($this->input('primary_category_id'))) {
                $validator->errors()->add(
                    'primary_category_id',
                    'The primary category must be included in category_ids.'
                );
            }
        });
    }
}
