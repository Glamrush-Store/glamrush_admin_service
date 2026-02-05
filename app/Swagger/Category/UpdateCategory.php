<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */


namespace App\Swagger\Category;

use OpenApi\Attributes as OA;


#[OA\Put(
    path: '/api/categories/{slug}',
    summary: 'Update a category',
    tags: ['Categories'],
    security: [['bearerAuth' => []]],
    parameters: [
        new OA\PathParameter(
            name: 'slug',
            required: true,
            schema: new OA\Schema(type: 'string'),
            example: 'ELECTRIC-APPLIANCES'
        ),
    ],
    requestBody: new OA\RequestBody(
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'is_active', type: 'boolean'),
                    new OA\Property(property: 'image', type: 'string', format: 'binary'),
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 200,
            description: 'Category updated',
            content: new OA\JsonContent(ref: '#/components/schemas/Category')
        ),
    ]
)]
class UpdateCategory {}
