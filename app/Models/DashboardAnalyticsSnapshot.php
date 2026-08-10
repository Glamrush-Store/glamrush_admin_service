<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DashboardAnalyticsSnapshot extends Model
{
    protected $fillable = [
        'period',
        'starts_at',
        'ends_at',
        'payload',
        'aggregated_at',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'payload' => 'array',
            'aggregated_at' => 'datetime',
        ];
    }
}
