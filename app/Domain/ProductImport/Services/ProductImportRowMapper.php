<?php

namespace App\Domain\ProductImport\Services;

use App\Domain\ProductImport\Exceptions\InvalidProductImportException;
use App\Models\ProductImport;
use App\Models\SkuAttributeCode;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ProductImportRowMapper
{
    private const PRODUCT_COLUMNS = [
        'name', 'type', 'status', 'short_description', 'description', 'price', 'sale_price',
        'sale_starts_at', 'sale_ends_at', 'manage_stock', 'stock_quantity', 'in_stock',
        'is_featured', 'sort_order', 'meta_title', 'meta_description', 'product_image_urls',
    ];

    public function map(array $rows, ProductImport $import): array
    {
        if ($rows === []) {
            throw new InvalidProductImportException('A product group cannot be empty.');
        }

        $productKey = (string) $rows[0]['product_key'];
        $product = $this->collapseProductColumns($rows, $productKey);
        $product['status'] = $product['status'] ?: $import->default_product_status;

        $validator = Validator::make($product, [
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::in(['simple', 'variable'])],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'is_featured' => ['nullable'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            throw new InvalidProductImportException(
                "Product {$productKey} is invalid.",
                $validator->errors()->toArray()
            );
        }

        $payload = [
            'name' => $product['name'],
            'type' => $product['type'],
            'status' => $product['status'],
            'vendor_id' => $import->vendor_id,
            'brand_id' => $import->brand_id,
            'category_ids' => $import->category_ids,
            'primary_category_id' => $import->primary_category_id,
            'category_sequences' => $import->category_sequences ?? [],
            'short_description' => $this->nullable($product['short_description']),
            'description' => $this->nullable($product['description']),
            'is_featured' => $this->boolean($product['is_featured'], 'is_featured', false),
            'sort_order' => $this->integer($product['sort_order'], 'sort_order', 0),
            'meta_title' => $this->nullable($product['meta_title']),
            'meta_description' => $this->nullable($product['meta_description']),
        ];

        $productImageUrls = $this->urls(
            $product['product_image_urls'],
            'product_image_urls',
            config('product_imports.max_product_images')
        );

        if ($product['type'] === 'simple') {
            if (count($rows) !== 1) {
                throw new InvalidProductImportException("Simple product {$productKey} must have exactly one CSV row.");
            }

            $payload = [...$payload, ...$this->priceAndStock($product, '')];

            return [
                'product_key' => $productKey,
                'row_numbers' => [$rows[0]['_row_number']],
                'data' => $payload,
                'product_image_urls' => $productImageUrls,
                'variant_image_urls' => [],
            ];
        }

        $variants = [];
        $variantImageUrls = [];
        $attributeSignatures = [];
        $defaultCount = 0;

        foreach ($rows as $index => $row) {
            $variant = $this->mapVariant($row);
            if ($variant['attributes'] !== []) {
                $signature = collect($variant['attributes'])
                    ->sortBy('type')
                    ->map(fn (array $attribute) => strtolower($attribute['type'].'='.$attribute['value']))
                    ->implode('|');

                if (in_array($signature, $attributeSignatures, true)) {
                    throw new InvalidProductImportException("Product {$productKey} contains a duplicate variant attribute combination.");
                }

                $attributeSignatures[] = $signature;
            }
            $defaultCount += $variant['is_default'] ? 1 : 0;
            $variants[] = $variant;
            $variantImageUrls[$index] = $this->urls(
                $row['variant_image_urls'] ?? '',
                'variant_image_urls',
                config('product_imports.max_variant_images')
            );
        }

        if ($defaultCount !== 1) {
            throw new InvalidProductImportException("Variable product {$productKey} must have exactly one default variant.");
        }

        $payload['variants'] = $variants;

        return [
            'product_key' => $productKey,
            'row_numbers' => array_column($rows, '_row_number'),
            'data' => $payload,
            'product_image_urls' => $productImageUrls,
            'variant_image_urls' => $variantImageUrls,
        ];
    }

    private function collapseProductColumns(array $rows, string $productKey): array
    {
        $product = [];

        foreach (self::PRODUCT_COLUMNS as $column) {
            $values = collect($rows)
                ->pluck($column)
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->uniqueStrict()
                ->values();

            if ($values->count() > 1) {
                throw new InvalidProductImportException(
                    "Product {$productKey} has conflicting values for {$column}."
                );
            }

            $product[$column] = $values->first() ?? '';
        }

        return $product;
    }

    private function mapVariant(array $row): array
    {
        $attributes = $this->attributes((string) ($row['variant_attributes'] ?? ''));
        $data = [
            'price' => $row['variant_price'] ?? '',
            'sale_price' => $row['variant_sale_price'] ?? '',
            'sale_starts_at' => $row['variant_sale_starts_at'] ?? '',
            'sale_ends_at' => $row['variant_sale_ends_at'] ?? '',
            'manage_stock' => $row['variant_manage_stock'] ?? '',
            'stock_quantity' => $row['variant_stock_quantity'] ?? '',
            'in_stock' => $row['variant_in_stock'] ?? '',
        ];

        $mapped = $this->priceAndStock($data, 'variant_');
        $mapped['attributes'] = $attributes;
        $mapped['is_default'] = $this->boolean($row['variant_is_default'] ?? '', 'variant_is_default');
        $mapped['status'] = ($row['variant_status'] ?? '') ?: 'active';
        $mapped['sort_order'] = $this->integer($row['variant_sort_order'] ?? '', 'variant_sort_order', 0);

        if (! in_array($mapped['status'], ['active', 'disabled'], true)) {
            throw new InvalidProductImportException('variant_status must be active or disabled.');
        }

        return $mapped;
    }

    private function priceAndStock(array $data, string $prefix): array
    {
        $source = fn (string $field) => $data[$prefix.$field] ?? $data[$field] ?? '';
        $price = $this->decimal($source('price'), $prefix.'price');
        $salePrice = $this->nullableDecimal($source('sale_price'), $prefix.'sale_price');
        $saleStartsAt = $this->date($source('sale_starts_at'), $prefix.'sale_starts_at');
        $saleEndsAt = $this->date($source('sale_ends_at'), $prefix.'sale_ends_at');
        $manageStock = $this->boolean($source('manage_stock'), $prefix.'manage_stock');
        $stockQuantity = $this->integer($source('stock_quantity'), $prefix.'stock_quantity', $manageStock ? null : 0);

        if ($salePrice !== null && $salePrice >= $price) {
            throw new InvalidProductImportException("{$prefix}sale_price must be lower than {$prefix}price.");
        }

        if ($saleStartsAt && $saleEndsAt && $saleEndsAt->lessThanOrEqualTo($saleStartsAt)) {
            throw new InvalidProductImportException("{$prefix}sale_ends_at must be after {$prefix}sale_starts_at.");
        }

        return [
            'price' => $price,
            'sale_price' => $salePrice,
            'sale_starts_at' => $saleStartsAt,
            'sale_ends_at' => $saleEndsAt,
            'manage_stock' => $manageStock,
            'stock_quantity' => $stockQuantity,
            'in_stock' => $this->boolean($source('in_stock'), $prefix.'in_stock'),
        ];
    }

    private function attributes(string $value): array
    {
        if (trim($value) === '') {
            return [];
        }

        $attributes = [];
        foreach (explode('|', $value) as $pair) {
            $parts = array_map('trim', explode('=', $pair, 2));
            if (count($parts) !== 2 || $parts[0] === '' || $parts[1] === '') {
                throw new InvalidProductImportException("Invalid variant attribute '{$pair}'. Use type=value.");
            }

            [$type, $attributeValue] = $parts;
            if (collect($attributes)->contains(fn (array $attribute) => strcasecmp($attribute['type'], $type) === 0)) {
                throw new InvalidProductImportException("Variant attribute type {$type} is repeated.");
            }

            if (! SkuAttributeCode::query()
                ->where('type', $type)
                ->where('value', $attributeValue)
                ->where('is_active', true)
                ->exists()) {
                throw new InvalidProductImportException("SKU code not defined for {$type}: {$attributeValue}.");
            }

            $attributes[] = ['type' => $type, 'value' => $attributeValue];
        }

        return $attributes;
    }

    private function urls(?string $value, string $field, int $limit): array
    {
        if ($value === null || trim($value) === '') {
            return [];
        }

        $urls = array_values(array_unique(array_filter(array_map('trim', explode('|', $value)))));
        if (count($urls) > $limit) {
            throw new InvalidProductImportException("{$field} accepts at most {$limit} URLs.");
        }

        foreach ($urls as $url) {
            if (filter_var($url, FILTER_VALIDATE_URL) === false || strtolower((string) parse_url($url, PHP_URL_SCHEME)) !== 'https') {
                throw new InvalidProductImportException("{$field} contains an invalid HTTPS URL: {$url}");
            }
        }

        return $urls;
    }

    private function boolean(mixed $value, string $field, ?bool $default = null): bool
    {
        if (($value === '' || $value === null) && $default !== null) {
            return $default;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        if ($parsed === null) {
            throw new InvalidProductImportException("{$field} must be true or false.");
        }

        return $parsed;
    }

    private function integer(mixed $value, string $field, ?int $default = null): int
    {
        if (($value === '' || $value === null) && $default !== null) {
            return $default;
        }

        $parsed = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
        if ($parsed === false) {
            throw new InvalidProductImportException("{$field} must be a nonnegative integer.");
        }

        return $parsed;
    }

    private function decimal(mixed $value, string $field): float
    {
        if ($value === '' || ! is_numeric($value) || (float) $value < 0) {
            throw new InvalidProductImportException("{$field} must be a nonnegative number.");
        }

        return round((float) $value, 2);
    }

    private function nullableDecimal(mixed $value, string $field): ?float
    {
        return $value === '' || $value === null ? null : $this->decimal($value, $field);
    }

    private function date(mixed $value, string $field): ?Carbon
    {
        if ($value === '' || $value === null) {
            return null;
        }

        try {
            return Carbon::parse((string) $value);
        } catch (\Throwable) {
            throw new InvalidProductImportException("{$field} must be a valid date.");
        }
    }

    private function nullable(mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }
}
