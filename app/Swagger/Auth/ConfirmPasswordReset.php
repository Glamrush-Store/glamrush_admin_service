<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Auth;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/password/reset/confirm',
    summary: 'Reset user password',
    description: 'Set a new password using a previously validated reset code',
    tags: ['Auth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['password', 'password_confirmation'],
            properties: [
                new OA\Property(
                    property: 'password',
                    type: 'string',
                    format: 'password',
                    minLength: 8,
                    description: 'Must contain at least one uppercase letter, one lowercase letter, and one number',
                    example: 'StrongPass1'
                ),
                new OA\Property(
                    property: 'password_confirmation',
                    type: 'string',
                    format: 'password',
                    example: 'StrongPass1'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Password reset successfully'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
    ]
)]

class ConfirmPasswordReset {}
