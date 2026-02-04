<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPasswordResetCodeRequest extends FormRequest
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
            'code' => [
                'required',
                'string',
                'max:6',
            ],
            'email' => [
                'required',
            ],
        ];
    }

    /**
     * Custom validation error messages (optional but nice for APIs)
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Email is required.',
            'email.email' => 'Enter a valid email address.',
            'code.max' => 'Invalid code entered.',
        ];
    }
}
