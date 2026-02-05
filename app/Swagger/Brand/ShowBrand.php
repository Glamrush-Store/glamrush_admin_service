<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Brand;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/brands/{slug}',
    summary: 'Get brand by slug',
    security: [['bearerAuth' => []]],
    tags: ['Brands'],
    parameters: [
        new OA\PathParameter(
            name: 'slug',
            required: true,
            schema: new OA\Schema(type: 'string'),
            example: 'apple'
        ),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Brand details',
            content: new OA\JsonContent(ref: '#/components/schemas/Brand')
        ),
        new OA\Response(response: 404, description: 'Brand not found'),
    ]
)]
class ShowBrand {}
