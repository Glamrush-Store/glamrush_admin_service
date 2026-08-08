<?php

namespace App\Models;

use App\Domain\Content\Concerns\HasPublicationState;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Faq extends Model
{
    use HasPublicationState, HasUlids, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['faq_category_id', 'question', 'answer', 'display_order', 'is_published', 'published_at', 'expires_at', 'applies_to_all_storefronts', 'created_by_admin_id', 'updated_by_admin_id'];

    protected $casts = ['display_order' => 'integer', 'is_published' => 'boolean', 'published_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'applies_to_all_storefronts' => 'boolean'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }

    public function storefronts(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'faq_storefronts')->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_admin_id');
    }
}
