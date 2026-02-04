<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Auth;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/logout',
    summary: 'Logout authenticated user',
    description: 'Invalidate the current access token and log the user out',
    tags: ['Auth'],
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Logout successful',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'message',
                        type: 'string',
                        example: 'Logged out successfully'
                    ),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
    ]
)]

class Logout {}
