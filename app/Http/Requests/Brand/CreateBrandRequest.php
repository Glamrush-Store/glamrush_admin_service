<?php

namespace App\Http\Requests\Brand;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateBrandRequest extends FormRequest
{

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:255',
            ],

            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('brands', 'slug'),
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'description' => [
                'nullable',
                'string',
            ],

            'meta_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_description' => [
                'nullable',
                'string',
                'max:255',
            ],

            'meta_keywords' => [
                'nullable',
                'string',
                'max:255',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
            ],

            'logo' => [
                'nullable',
                'image',
                'max:2048', // 2MB
            ],
        ];
    }
}
