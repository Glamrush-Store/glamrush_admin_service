<?php

namespace App\Http\Requests\AccessControl;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class SyncRolePermissionsRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'permissions' => ['required', 'array', 'max:500'],
            'permissions.*' => ['required', 'string', 'distinct', Rule::exists('permissions', 'name')->where('guard_name', 'sanctum')],
        ];
    }
}
