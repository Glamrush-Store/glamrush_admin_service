<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Auth;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/self',
    summary: 'Get authenticated user',
    tags: ['Auth'],
    security: [['sanctum' => []]],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Authenticated user',
            content: new OA\JsonContent(ref: '#/components/schemas/UserResponse')
        ),
        new OA\Response(
            response: 401,
            description: 'Unauthenticated'
        ),
    ]
)]

class GetSelf {}
