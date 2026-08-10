<?php

namespace App\Http\Requests\AccessControl;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateUserRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('email')) {
            $this->merge(['email' => str($this->input('email'))->trim()->lower()->value()]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->route('user'))],
            'password' => ['sometimes', 'required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role_id' => ['sometimes', 'required', 'integer', Rule::exists('roles', 'id')->where('guard_name', 'sanctum')],
        ];
    }
}
