<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Auth;

use OpenApi\Attributes as OA;

class Login
{
    #[OA\Tag(
        name: 'Auth',
        description: 'Authentication Endpoints',

    )]
    #[OA\Post(
        path: '/api/account/login',
        summary: 'Authenticate user and get token',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: ['email', 'password'],
                properties: [
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        example: 'john@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        example: 'password123'
                    ),
                    new OA\Property(
                        property: 'device_id',
                        type: 'string',
                        example: 'eeeseioi6hhttiiop'
                    ),
                    new OA\Property(
                        property: 'device_name',
                        type: 'string',
                        example: 'Samsung Galaxy S20'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 200,
                description: 'Login successful',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'access_token',
                            type: 'string',
                            example: 'eyJhbGciOiJIUz...'
                        ),
                        new OA\Property(
                            property: 'token_type',
                            type: 'string',
                            example: 'bearer'
                        ),
                    ]
                )
            ),
            new OA\Response(
                response: 401,
                description: 'Invalid credentials',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(
                            property: 'error',
                            type: 'string',
                            example: 'Invalid email/password'
                        ),
                    ]
                )
            ),
        ]
    )]
    public function login() {}
}
