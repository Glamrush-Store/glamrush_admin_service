<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;

class Vendor extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\VendorFactory> */
    use HasFactory;
    use HasRoles;

    use HasUlids;

    protected $keyType = 'string';

    public $incrementing = false;

    protected $fillable = [
        'name',
        'business_name',
        'email',
        'phone',
        'password',
        'code',
        'is_active',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'last_stock_sync_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_stock_sync_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Vendor owns many products
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
