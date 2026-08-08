<?php

namespace App\Models;

use App\Domain\Discount\Enums\DiscountTargetMode;
use App\Domain\Discount\Enums\DiscountTargetType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscountCodeTarget extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['target_type', 'target_id', 'mode'];

    protected $casts = ['target_type' => DiscountTargetType::class, 'mode' => DiscountTargetMode::class];

    public function discountCode(): BelongsTo
    {
        return $this->belongsTo(DiscountCode::class);
    }
}
