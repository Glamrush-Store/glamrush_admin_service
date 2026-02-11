<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Category;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/categories/{slug}',
    summary: 'Get a category by slug',
    tags: ['Categories'],
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\PathParameter(
            name: 'slug',
            required: true,
            schema: new OA\Schema(type: 'string'),
            example: 'electronics'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Category details',
            content: new OA\JsonContent(ref: '#/components/schemas/Category')
        ),
        new OA\Response(
            response: 404,
            description: 'Category not found'
        ),
    ]
)]
class ShowCategory {}
