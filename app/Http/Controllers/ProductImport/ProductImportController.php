<?php

namespace App\Http\Controllers\ProductImport;

use App\Http\Requests\ProductImport\StoreProductImportRequest;
use App\Http\Resources\ProductImport\ProductImportResource;
use App\Http\Responses\ApiResponse;
use App\Jobs\ProcessProductImportJob;
use App\Models\ProductImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductImportController
{
    public function index(Request $request)
    {
        $imports = ProductImport::query()
            ->latest()
            ->paginate(min(max((int) $request->query('per_page', 15), 1), 100));

        return ApiResponse::success(ProductImportResource::collection($imports));
    }

    public function store(StoreProductImportRequest $request)
    {
        $file = $request->file('file');
        $validated = $request->validated();

        $import = ProductImport::query()->create([
            'user_id' => $request->user()->id,
            'vendor_id' => $validated['vendor_id'],
            'brand_id' => $validated['brand_id'],
            'category_ids' => $validated['category_ids'],
            'primary_category_id' => $validated['primary_category_id'],
            'category_sequences' => $validated['category_sequences'] ?? [],
            'default_product_status' => $validated['default_status'],
            'image_failure_policy' => $validated['image_failure_policy'] ?? 'warn',
            'status' => ProductImport::STATUS_PENDING,
            'original_filename' => $file->getClientOriginalName(),
            'file_hash' => hash_file('sha256', $file->getRealPath()),
        ]);

        try {
            $path = $file->storeAs(
                'product-imports/'.$import->id,
                'source.csv',
                config('product_imports.disk')
            );

            if ($path === false) {
                throw new \RuntimeException('Unable to store the uploaded CSV file.');
            }

            $import->update(['file_path' => $path]);
        } catch (Throwable $exception) {
            Storage::disk(config('product_imports.disk'))->deleteDirectory('product-imports/'.$import->id);
            $import->delete();
            throw $exception;
        }

        ProcessProductImportJob::dispatch($import->id)->afterResponse();

        return ApiResponse::success(
            new ProductImportResource($import->refresh()),
            'Product import queued.',
            202
        );
    }

    public function show(ProductImport $productImport)
    {
        return ApiResponse::success(new ProductImportResource($productImport));
    }

    public function template()
    {
        return response()->download(
            resource_path('files/product-import-sample.csv'),
            'product-import-sample.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8']
        );
    }
}
