<?php

/*
 * © 2026 Demilade Oyewusi
 * Licensed under the MIT License.
 * See the LICENSE file for details.
 */

namespace App\Swagger\Brand\Schemas;

use OpenApi\Attributes as OA;

#[OA\Schema(
    schema: 'Brand',
    type: 'object',
    required: ['id', 'name', 'slug', 'is_active'],
)]
class Brand
{
    #[OA\Property(example: '01KGNEX9KQZJ7T0A2YH9ZB3R4M')]
    public string $id;

    #[OA\Property(example: 'Apple')]
    public string $name;

    #[OA\Property(example: 'apple')]
    public string $slug;

    #[OA\Property(example: true)]
    public bool $is_active;

    #[OA\Property(nullable: true)]
    public ?string $description;

    #[OA\Property(nullable: true)]
    public ?string $meta_title;

    #[OA\Property(nullable: true)]
    public ?string $meta_description;

    #[OA\Property(nullable: true)]
    public ?string $meta_keywords;

    #[OA\Property(example: 1)]
    public int $sort_order;

    #[OA\Property(
        nullable: true,
        example: 'https://cdn.example.com/media/brands/apple.png'
    )]
    public ?string $logo_url;

    #[OA\Property(format: 'date-time')]
    public string $created_at;

    #[OA\Property(format: 'date-time')]
    public string $updated_at;
}
