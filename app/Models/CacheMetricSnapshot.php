<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class CacheMetricSnapshot extends Model
{
    use HasUlids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'service_name',
        'area',
        'bucket_started_at',
        'bucket_ended_at',
        'hits',
        'misses',
        'writes',
        'forgets',
        'hit_ratio',
        'redis_metrics',
    ];

    protected $casts = [
        'bucket_started_at' => 'datetime',
        'bucket_ended_at' => 'datetime',
        'hits' => 'integer',
        'misses' => 'integer',
        'writes' => 'integer',
        'forgets' => 'integer',
        'hit_ratio' => 'float',
        'redis_metrics' => 'array',
    ];
}
