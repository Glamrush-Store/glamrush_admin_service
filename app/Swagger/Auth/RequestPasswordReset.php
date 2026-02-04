<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Auth;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/password/reset/request',
    summary: 'Request password reset',
    description: "Send a password reset  OTP to the user's email address",
    tags: ['Auth'],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['email'],
            properties: [
                new OA\Property(
                    property: 'email',
                    type: 'string',
                    format: 'email',
                    example: 'john@example.com'
                ),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Password reset request accepted'
        ),
        new OA\Response(
            response: 422,
            description: 'Validation error'
        ),
    ]
)]

class RequestPasswordReset {}
