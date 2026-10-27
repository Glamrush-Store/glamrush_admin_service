<?php

namespace App\Domain\ProductImport\Services;

use App\Domain\ProductImport\Exceptions\InvalidProductImportException;
use SplFileObject;

class ProductImportCsvParser
{
    private const REQUIRED_HEADERS = ['product_key', 'name', 'type'];

    private const FORBIDDEN_RELATION_HEADERS = [
        'brand', 'brand_id', 'brand_code',
        'vendor', 'vendor_id', 'vendor_code',
        'category', 'category_id', 'category_ids', 'category_slugs', 'primary_category_slug',
    ];

    public function parse(string $path): array
    {
        if (! is_file($path) || ! is_readable($path)) {
            throw new InvalidProductImportException('The uploaded CSV file cannot be read.');
        }

        $file = new SplFileObject($path, 'rb');
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl(',', '"', '\\');

        $headers = $file->fgetcsv();

        if ($headers === false || $headers === [null]) {
            throw new InvalidProductImportException('The CSV file is empty.');
        }

        $headers = array_map(function ($header, int $index): string {
            $header = trim((string) $header);

            if ($index === 0) {
                $header = preg_replace('/^\xEF\xBB\xBF/', '', $header) ?? $header;
            }

            return strtolower($header);
        }, $headers, array_keys($headers));

        if (in_array('', $headers, true)) {
            throw new InvalidProductImportException('CSV headers cannot be empty.');
        }

        if (count($headers) !== count(array_unique($headers))) {
            throw new InvalidProductImportException('The CSV contains duplicate column headers.');
        }

        $missing = array_values(array_diff(self::REQUIRED_HEADERS, $headers));
        if ($missing !== []) {
            throw new InvalidProductImportException(
                'The CSV is missing required columns: '.implode(', ', $missing).'.'
            );
        }

        $forbidden = array_values(array_intersect(self::FORBIDDEN_RELATION_HEADERS, $headers));
        if ($forbidden !== []) {
            throw new InvalidProductImportException(
                'Catalog relationships must be selected in the upload configuration, not supplied in CSV columns: '.implode(', ', $forbidden).'.'
            );
        }

        $groups = [];
        $rowCount = 0;

        while (! $file->eof()) {
            $values = $file->fgetcsv();

            if ($values === false || $values === [null] || $this->isBlankRow($values)) {
                continue;
            }

            $rowCount++;
            if ($rowCount > config('product_imports.max_rows')) {
                throw new InvalidProductImportException(
                    'The CSV exceeds the maximum of '.config('product_imports.max_rows').' data rows.'
                );
            }

            if (count($values) !== count($headers)) {
                throw new InvalidProductImportException(
                    "CSV row {$file->key()} has ".count($values).' columns; '.count($headers).' were expected.'
                );
            }

            $row = array_map(fn ($value) => is_string($value) ? trim($value) : $value, array_combine($headers, $values));
            $row['_row_number'] = $file->key();
            $productKey = trim((string) ($row['product_key'] ?? ''));

            if ($productKey === '') {
                throw new InvalidProductImportException("CSV row {$file->key()} has no product_key.");
            }

            $groups[$productKey][] = $row;
        }

        if ($rowCount === 0) {
            throw new InvalidProductImportException('The CSV contains no product rows.');
        }

        return [
            'groups' => $groups,
            'row_count' => $rowCount,
        ];
    }

    private function isBlankRow(array $row): bool
    {
        return collect($row)->every(fn ($value) => trim((string) $value) === '');
    }
}
