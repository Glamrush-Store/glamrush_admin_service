<?php

namespace App\Http\Requests\AccessControl;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class ListUsersRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'search' => ['nullable', 'string', 'max:100'],
            'role_id' => ['nullable', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'sanctum')],
            'sort' => ['nullable', Rule::in(['name', 'email', 'created_at', 'updated_at'])],
            'direction' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }
}
