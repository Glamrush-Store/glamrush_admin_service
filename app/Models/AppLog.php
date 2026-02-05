<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppLog extends Model
{
    protected $fillable = [
        'level',
        'event',
        'message',
        'context',
        'actor_type',
        'actor_id',
        'actor_name',
    ];

    protected $casts = [
        'context' => 'array',
    ];
}
