<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Auth;

use OpenApi\Attributes as OA;

class CreateAccount
{
    #[OA\Post(
        path: '/api/account/create',
        summary: 'Register a new user',
        tags: ['Auth'],
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(
                required: [
                    'name',
                    'email',
                    'password',
                    'password_confirmation',
                    'device_id',
                    'role',
                ],
                properties: [
                    new OA\Property(
                        property: 'name',
                        type: 'string',
                        maxLength: 255,
                        example: 'John Doe'
                    ),
                    new OA\Property(
                        property: 'email',
                        type: 'string',
                        format: 'email',
                        example: 'john@example.com'
                    ),
                    new OA\Property(
                        property: 'password',
                        type: 'string',
                        format: 'password',
                        minLength: 8,
                        example: 'password123'
                    ),
                    new OA\Property(
                        property: 'password_confirmation',
                        type: 'string',
                        format: 'password',
                        example: 'password123'
                    ),
                    new OA\Property(
                        property: 'device_id',
                        type: 'string',
                        maxLength: 255,
                        example: 'eeeseioi6hhttiiop'
                    ),
                    new OA\Property(
                        property: 'device_name',
                        type: 'string',
                        maxLength: 255,
                        nullable: true,
                        example: 'Samsung Galaxy S20'
                    ),
                    new OA\Property(
                        property: 'role',
                        type: 'string',
                        example: 'admin'
                    ),
                ]
            )
        ),
        responses: [
            new OA\Response(
                response: 201,
                description: 'Account created successfully'
            ),
            new OA\Response(
                response: 422,
                description: 'Validation error'
            ),
        ]
    )]
    public function createAccount() {}
}
