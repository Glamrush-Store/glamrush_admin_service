<?php

namespace App\Http\Requests\AccessControl;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge(['name' => str($this->input('name'))->trim()->lower()->snake()->value()]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('roles', 'name')->where('guard_name', 'sanctum')->ignore($this->route('role'))],
            'permissions' => ['sometimes', 'array', 'max:500'],
            'permissions.*' => ['required', 'string', 'distinct', Rule::exists('permissions', 'name')->where('guard_name', 'sanctum')],
        ];
    }
}
