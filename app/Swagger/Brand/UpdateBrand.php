<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Brand;

use OpenApi\Attributes as OA;

#[OA\Put(
    path: '/api/brands/{brand}',
    summary: 'Update a brand',
    tags: ['Brands'],
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\PathParameter(
            name: 'brand',
            required: true,
            schema: new OA\Schema(type: 'string'),
            example: 'apple'
        ),
    ],
    requestBody: new OA\RequestBody(
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'is_active', type: 'boolean'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'meta_title', type: 'string', nullable: true),
                    new OA\Property(property: 'meta_description', type: 'string', nullable: true),
                    new OA\Property(property: 'meta_keywords', type: 'string', nullable: true),
                    new OA\Property(property: 'sort_order', type: 'integer', nullable: true),
                    new OA\Property(property: 'logo', type: 'string', format: 'binary'),
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Brand updated',
            content: new OA\JsonContent(ref: '#/components/schemas/Brand')
        ),
        new OA\Response(response: 404, description: 'Brand not found'),
    ]
)]
class UpdateBrand {}
