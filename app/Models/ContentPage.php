<?php

namespace App\Models;

use App\Domain\Content\Concerns\HasPublicationState;
use App\Domain\Content\Enums\ContentPageType;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class ContentPage extends Model implements HasMedia
{
    use HasPublicationState, HasUlids, InteractsWithMedia, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['slug', 'title', 'navigation_title', 'excerpt', 'content', 'page_type', 'settings', 'meta_title', 'meta_description', 'is_published', 'published_at', 'expires_at', 'applies_to_all_storefronts', 'display_order', 'created_by_admin_id', 'updated_by_admin_id'];

    protected function casts(): array
    {
        return ['page_type' => ContentPageType::class, 'settings' => 'array', 'is_published' => 'boolean', 'published_at' => 'immutable_datetime', 'expires_at' => 'immutable_datetime', 'applies_to_all_storefronts' => 'boolean', 'display_order' => 'integer'];
    }

    public function storefronts(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'content_page_storefronts')->withTimestamps();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_admin_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by_admin_id');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('content-images')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
