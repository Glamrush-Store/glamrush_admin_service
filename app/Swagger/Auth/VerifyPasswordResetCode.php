<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Auth;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/password/reset/validate',
    summary: 'Validate password reset code',
    description: "Validate the password reset code sent to the user's email address",
    tags: ['Auth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email', 'code'],
            properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    example: 'john@example.com'
                ),
                new OA\Property(
                    property: 'code',
                    type: 'string',
                    maxLength: 6,
                    example: '483920'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Reset code is valid'
        ),
        new OA\Response(
            response: 400,
            description: 'Invalid or expired reset code'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
    ]
)]

class VerifyPasswordResetCode {}
