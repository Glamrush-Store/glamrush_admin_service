<?php
/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Category;

use OpenApi\Attributes as OA;

#[OA\Post(
    path: '/api/categories',
    summary: 'Create a category',
    tags: ['Categories'],
    security: [['bearerAuth' => []]],
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\MediaType(
            mediaType: 'multipart/form-data',
            schema: new OA\Schema(
                required: ['name', 'is_active'],
                properties: [
                    new OA\Property(property: 'name', type: 'string'),
                    new OA\Property(property: 'parent_id', type: 'string', nullable: true),
                    new OA\Property(property: 'is_active', type: 'boolean'),
                    new OA\Property(property: 'description', type: 'string', nullable: true),
                    new OA\Property(property: 'meta_title', type: 'string', nullable: true),
                    new OA\Property(property: 'meta_description', type: 'string', nullable: true),
                    new OA\Property(property: 'meta_keywords', type: 'string', nullable: true),
                    new OA\Property(property: 'sort_order', type: 'integer', nullable: true),
                    new OA\Property(property: 'image', type: 'string', format: 'binary'),
                ]
            )
        )
    ),
    responses: [
        new OA\Response(
            response: 201,
            description: 'Category created',
            content: new OA\JsonContent(ref: '#/components/schemas/Category')
        ),
        new OA\Response(response: 422, description: 'Validation error'),
    ]
)]

class Category
{

}
