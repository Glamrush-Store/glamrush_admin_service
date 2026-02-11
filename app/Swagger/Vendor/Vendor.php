<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Vendor;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/vendors',
    tags: ['Vendors'],
    summary: 'Create a vendor',
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['name', 'email', 'password', 'code'],
            properties: [
                new OA\Property(property: 'name', type: 'string', example: 'John Doe'),
                new OA\Property(property: 'business_name', type: 'string', example: 'Glamrush Store'),
                new OA\Property(property: 'email', type: 'string', example: 'vendor@glamrush.com'),
                new OA\Property(property: 'password', type: 'string', example: 'secret123'),
                new OA\Property(property: 'code', type: 'string', example: 'GLAM1234'),
            ]
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Vendor created',
            content: new OA\JsonContent(ref: '#/components/schemas/Vendor')
        ),
    ]
)]
class Vendor {}
