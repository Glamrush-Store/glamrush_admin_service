<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Product\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'ProductVariant',
    type: 'object',
    required: ['id', 'sku', 'price', 'is_default']
)]
class ProductVariant
{
    #[OA\Property(example: 10)]
    public int $id;

    #[OA\Property(example: 'IP15-BLK-128')]
    public string $sku;

    #[OA\Property(example: 1200)]
    public float $price;

    #[OA\Property(example: true)]
    public bool $is_default;

    #[OA\Property(
        type: 'object',
        example: ['color' => 'black', 'storage' => '128GB']
    )]
    public array $attributes;
}
