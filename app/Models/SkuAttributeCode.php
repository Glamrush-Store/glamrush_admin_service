<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SkuAttributeCode extends Model
{
    use HasFactory;

    /** @use HasFactory<\Database\Factories\SkuAttributeCodeFactory> */
    protected $fillable = [
        'type',
        'value',
        'code',
        'is_active',
    ];
}
