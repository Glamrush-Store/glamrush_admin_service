<?php

use App\Domain\ProductImport\Exceptions\InvalidProductImportException;
use App\Domain\ProductImport\Services\RemoteProductImageDownloader;
use Illuminate\Support\Facades\Http;

uses(Tests\TestCase::class);

it('rejects non-https and private image URLs', function (string $url) {
    expect(fn () => app(RemoteProductImageDownloader::class)->download($url))
        ->toThrow(InvalidProductImportException::class);
})->with([
    'plain HTTP' => 'http://example.com/image.jpg',
    'localhost' => 'https://localhost/image.jpg',
    'loopback address' => 'https://127.0.0.1/image.jpg',
]);

it('downloads a supported public image into a temporary upload', function () {
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII=');
    Http::fake([
        'https://example.com/image.png' => Http::response($png, 200, ['Content-Type' => 'image/png']),
    ]);

    $file = app(RemoteProductImageDownloader::class)->download('https://example.com/image.png');

    try {
        expect($file->getMimeType())->toBe('image/png')
            ->and($file->getClientOriginalExtension())->toBe('png');
    } finally {
        @unlink($file->getPathname());
    }
});
