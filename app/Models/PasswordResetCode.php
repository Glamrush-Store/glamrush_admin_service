<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PasswordResetCode extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;

    protected $fillable = ['user_id', 'code_hash', 'expires_at', 'verified_at'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected $casts = [
        'expires_at' => 'datetime',
        'verified_at' => 'datetime',
    ];
}
