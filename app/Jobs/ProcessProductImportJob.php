<?php

namespace App\Jobs;

use App\Domain\ProductImport\Services\ProductImportProcessor;
use App\Models\ProductImport;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Throwable;

class ProcessProductImportJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 900;

    public function __construct(public readonly string $productImportId)
    {
        $this->onQueue(config('product_imports.queue'));
    }

    public function handle(ProductImportProcessor $processor): void
    {
        $processor->process(ProductImport::query()->findOrFail($this->productImportId));
    }

    public function failed(?Throwable $exception): void
    {
        ProductImport::query()->whereKey($this->productImportId)->update([
            'status' => ProductImport::STATUS_FAILED,
            'completed_at' => now(),
        ]);
    }
}
