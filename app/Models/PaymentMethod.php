<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    use HasUlids;

    public const SUPPORTED_CODES = [
        'bachs',
        'paystack',
        'flutterwave',
        'pay_on_delivery',
        'cash',
        'pos',
        'bank_transfer',
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'code',
        'description',
        'is_active',
        'sort_order',
        'public_config',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'public_config' => 'array',
        ];
    }
}
