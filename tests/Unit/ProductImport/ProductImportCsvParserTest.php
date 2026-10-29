<?php

use App\Domain\ProductImport\Exceptions\InvalidProductImportException;
use App\Domain\ProductImport\Services\ProductImportCsvParser;

uses(Tests\TestCase::class);

function productImportCsv(string $contents): string
{
    $path = tempnam(sys_get_temp_dir(), 'csv-test-');
    file_put_contents($path, $contents);

    return $path;
}

it('groups csv rows by product key and accepts a UTF-8 BOM', function () {
    $path = productImportCsv("\xEF\xBB\xBFproduct_key,name,type,variant_price\nP001,One,simple,\nP002,Two,variable,10\nP002,Two,variable,20\n");

    try {
        $result = app(ProductImportCsvParser::class)->parse($path);

        expect($result['row_count'])->toBe(3)
            ->and($result['groups'])->toHaveKeys(['P001', 'P002'])
            ->and($result['groups']['P002'])->toHaveCount(2);
    } finally {
        @unlink($path);
    }
});

it('rejects catalog relationship columns in the csv', function () {
    $path = productImportCsv("product_key,name,type,brand_id\nP001,One,simple,brand-id\n");

    try {
        expect(fn () => app(ProductImportCsvParser::class)->parse($path))
            ->toThrow(InvalidProductImportException::class, 'upload configuration');
    } finally {
        @unlink($path);
    }
});

it('rejects rows whose column count does not match the header', function () {
    $path = productImportCsv("product_key,name,type\nP001,One,simple,unexpected\n");

    try {
        expect(fn () => app(ProductImportCsvParser::class)->parse($path))
            ->toThrow(InvalidProductImportException::class, 'columns');
    } finally {
        @unlink($path);
    }
});

it('parses the bundled product import template', function () {
    $result = app(ProductImportCsvParser::class)->parse(
        resource_path('files/product-import-sample.csv')
    );

    expect($result['row_count'])->toBe(71)
        ->and($result['groups'])->toHaveCount(71)
        ->and($result['groups'])->toHaveKeys(['TOSD001', 'TOSD071'])
        ->and($result['groups']['TOSD001'])->toHaveCount(1)
        ->and($result['groups']['TOSD071'])->toHaveCount(1);
});
