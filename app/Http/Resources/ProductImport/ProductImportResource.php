<?php

namespace App\Http\Resources\ProductImport;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductImportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'configuration' => [
                'vendor_id' => $this->vendor_id,
                'brand_id' => $this->brand_id,
                'category_ids' => $this->category_ids ?? [],
                'primary_category_id' => $this->primary_category_id,
                'category_sequences' => $this->category_sequences ?? [],
                'default_status' => $this->default_product_status,
                'image_failure_policy' => $this->image_failure_policy,
            ],
            'original_filename' => $this->original_filename,
            'progress' => [
                'total_rows' => (int) $this->total_rows,
                'total_products' => (int) $this->total_products,
                'processed_products' => (int) $this->processed_products,
                'imported_products' => (int) $this->imported_products,
                'failed_products' => (int) $this->failed_products,
                'warning_count' => (int) $this->warning_count,
            ],
            'issues' => $this->issues ?? [],
            'started_at' => optional($this->started_at)->toISOString(),
            'completed_at' => optional($this->completed_at)->toISOString(),
            'created_at' => optional($this->created_at)->toISOString(),
            'updated_at' => optional($this->updated_at)->toISOString(),
        ];
    }
}
