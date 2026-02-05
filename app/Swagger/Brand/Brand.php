<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Brand;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/brands',
    summary: 'Create a brand',
    tags: ['Brands'],
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['name', 'is_active'],
                properties: [
                    new OA\Property(property: 'name', type: 'string', example: 'Apple'),
                    new OA\Property(property: 'is_active', type: 'boolean', example: true),
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
            response: 201,
            description: 'Brand created',
            content: new OA\JsonContent(ref: '#/components/schemas/Brand')
        ),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]
class Brand {}
