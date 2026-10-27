<?php

namespace App\Domain\ProductImport\Services;

use App\Domain\Product\UseCases\CreateProductUseCase;
use App\Domain\ProductImport\Exceptions\InvalidProductImportException;
use App\Models\ProductImport;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductImportProcessor
{
    public function __construct(
        private readonly ProductImportCsvParser $parser,
        private readonly ProductImportRowMapper $mapper,
        private readonly RemoteProductImageDownloader $imageDownloader,
        private readonly CreateProductUseCase $createProduct,
    ) {}

    public function process(ProductImport $import): void
    {
        $import->update([
            'status' => ProductImport::STATUS_PROCESSING,
            'started_at' => now(),
            'completed_at' => null,
            'issues' => [],
        ]);

        try {
            $parsed = $this->parser->parse(
                Storage::disk(config('product_imports.disk'))->path($import->file_path)
            );

            $import->update([
                'total_rows' => $parsed['row_count'],
                'total_products' => count($parsed['groups']),
            ]);

            foreach ($parsed['groups'] as $productKey => $rows) {
                $this->processProduct($import, (string) $productKey, $rows);
            }

            $import->refresh();
            $status = match (true) {
                $import->failed_products > 0 => ProductImport::STATUS_COMPLETED_WITH_ERRORS,
                $import->warning_count > 0 => ProductImport::STATUS_COMPLETED_WITH_WARNINGS,
                default => ProductImport::STATUS_COMPLETED,
            };

            $import->update([
                'status' => $status,
                'completed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            $this->markFailed($import, $exception);
            throw $exception;
        }
    }

    private function processProduct(ProductImport $import, string $productKey, array $rows): void
    {
        $temporaryFiles = [];
        $warnings = [];

        try {
            $mapped = $this->mapper->map($rows, $import);

            $mapped['data']['photos'] = $this->downloadImages(
                $mapped['product_image_urls'],
                $temporaryFiles,
                $warnings,
                $import
            );

            foreach ($mapped['data']['variants'] ?? [] as $index => &$variant) {
                $variant['photos'] = $this->downloadImages(
                    $mapped['variant_image_urls'][$index] ?? [],
                    $temporaryFiles,
                    $warnings,
                    $import
                );
            }
            unset($variant);

            $this->createProduct->execute($mapped['data'], allowAttributeLessVariants: true);

            foreach ($warnings as $warning) {
                $this->appendIssue($import, [
                    'severity' => 'warning',
                    'product_key' => $productKey,
                    'rows' => $mapped['row_numbers'],
                    ...$warning,
                ]);
            }

            $import->increment('imported_products');
        } catch (Throwable $exception) {
            $this->appendIssue($import, [
                'severity' => 'error',
                'product_key' => $productKey,
                'rows' => array_column($rows, '_row_number'),
                'message' => $exception->getMessage(),
                'errors' => $exception instanceof InvalidProductImportException ? $exception->errors : [],
            ]);
            $import->increment('failed_products');
        } finally {
            $import->increment('processed_products');
            $this->deleteTemporaryFiles($temporaryFiles);
        }
    }

    private function downloadImages(
        array $urls,
        array &$temporaryFiles,
        array &$warnings,
        ProductImport $import
    ): array {
        $files = [];

        foreach ($urls as $url) {
            try {
                $file = $this->imageDownloader->download($url);
                $files[] = $file;
                $temporaryFiles[] = $file->getPathname();
            } catch (Throwable $exception) {
                if ($import->image_failure_policy === 'fail_product') {
                    throw new InvalidProductImportException(
                        "Unable to import image {$url}: {$exception->getMessage()}"
                    );
                }

                $warnings[] = [
                    'field' => 'image_url',
                    'value' => $url,
                    'message' => $exception->getMessage(),
                ];
            }
        }

        return $files;
    }

    private function appendIssue(ProductImport $import, array $issue): void
    {
        $import->refresh();
        $issues = $import->issues ?? [];
        $issues[] = $issue;

        $updates = ['issues' => $issues];
        if (($issue['severity'] ?? null) === 'warning') {
            $updates['warning_count'] = $import->warning_count + 1;
        }

        $import->update($updates);
    }

    private function markFailed(ProductImport $import, Throwable $exception): void
    {
        $issues = $import->issues ?? [];
        $issues[] = [
            'severity' => 'error',
            'product_key' => null,
            'rows' => [],
            'message' => $exception->getMessage(),
            'errors' => $exception instanceof InvalidProductImportException ? $exception->errors : [],
        ];

        $import->update([
            'status' => ProductImport::STATUS_FAILED,
            'issues' => $issues,
            'completed_at' => now(),
        ]);
    }

    private function deleteTemporaryFiles(array $paths): void
    {
        foreach (array_unique($paths) as $path) {
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
