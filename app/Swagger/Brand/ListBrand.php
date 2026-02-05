<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Brand;

use OpenApi\Attributes as OA;

#[OA\Get(
    path: '/api/brands',
    summary: 'List brands',
    security: [['bearerAuth' => []]],
    tags: ['Brands'],
    parameters: [
        new OA\QueryParameter(name: 'search', schema: new OA\Schema(type: 'string')),
        new OA\QueryParameter(name: 'is_active', schema: new OA\Schema(type: 'boolean')),
        new OA\QueryParameter(name: 'per_page', schema: new OA\Schema(type: 'integer', default: 15)),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Paginated list of brands',
            content: new OA\JsonContent(
                type: 'object',
                properties: [
                    new OA\Property(
                        property: 'data',
                        type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/Brand')
                    ),
                    new OA\Property(property: 'links', type: 'object'),
                    new OA\Property(property: 'meta', type: 'object'),
                ]
            )
        ),
    ]
)]
class ListBrand {}
