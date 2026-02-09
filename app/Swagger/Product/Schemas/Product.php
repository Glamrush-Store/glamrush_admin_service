<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Product\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Product',
    type: 'object',
    required: ['id', 'name', 'slug', 'type', 'status'],
)]
class Product
{
    #[OA\Property(example: 1)]
    public int $id;

    #[OA\Property(example: 'iPhone 15')]
    public string $name;

    #[OA\Property(example: 'iphone-15')]
    public string $slug;

    #[OA\Property(
        description: 'simple | variable | digital | service',
        example: 'variable'
    )]
    public string $type;

    #[OA\Property(
        description: 'published | draft | archived',
        example: 'published'
    )]
    public string $status;

    #[OA\Property(example: 1200)]
    public ?float $price;

    #[OA\Property(example: true)]
    public bool $is_featured;

    #[OA\Property(example: 1)]
    public int $sort_order;

    #[OA\Property(type: 'array', items: new OA\Items(ref: '#/components/schemas/ProductVariant'))]
    public array $variants;
}
