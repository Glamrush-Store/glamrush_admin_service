<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class ProductImport extends Model
{
    use HasUlids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_COMPLETED_WITH_WARNINGS = 'completed_with_warnings';

    public const STATUS_COMPLETED_WITH_ERRORS = 'completed_with_errors';

    public const STATUS_FAILED = 'failed';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'vendor_id',
        'brand_id',
        'category_ids',
        'primary_category_id',
        'category_sequences',
        'status',
        'default_product_status',
        'image_failure_policy',
        'original_filename',
        'file_path',
        'file_hash',
        'total_rows',
        'total_products',
        'processed_products',
        'imported_products',
        'failed_products',
        'warning_count',
        'issues',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'category_ids' => 'array',
        'category_sequences' => 'array',
        'issues' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function primaryCategory()
    {
        return $this->belongsTo(Category::class, 'primary_category_id');
    }
}
