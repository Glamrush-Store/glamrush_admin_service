<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\Auth;

use App\Http\Requests\ApiRequest;

class ConfirmPasswordResetRequest extends ApiRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Login is public
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).+$/',
            ],
        ];
    }

    /**
     * Custom validation error messages (optional but nice for APIs)
     */
    public function messages(): array
    {
        return [
            'password.required' => 'password is required.',
            'password.confirmed' => 'password does not match.',
            'password.regex' => 'password must contain at least one uppercase letter, one lowercase letter and one digit.',
            'password.min' => 'password must be at least 8 characters long.',
        ];
    }
}
