<?php

namespace App\Http\Requests\AccessControl;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class CreateRoleRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => str($this->input('name'))->trim()->lower()->snake()->value()]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('roles', 'name')->where('guard_name', 'sanctum')],
            'permissions' => ['sometimes', 'array', 'max:500'],
            'permissions.*' => ['required', 'string', 'distinct', Rule::exists('permissions', 'name')->where('guard_name', 'sanctum')],
        ];
    }
}
