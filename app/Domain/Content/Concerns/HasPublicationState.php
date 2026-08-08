<?php

namespace App\Domain\Content\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasPublicationState
{
    public function state(): string
    {
        if ($this->expires_at?->isPast()) {
            return 'expired';
        }
        if ($this->is_published && $this->published_at?->isFuture()) {
            return 'scheduled';
        }
        if ($this->is_published) {
            return 'published';
        }
        if ($this->published_at === null) {
            return 'draft';
        }

        return 'unpublished';
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeScheduled(Builder $query): Builder
    {
        return $query->where('is_published', true)->where('published_at', '>', now());
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->whereNotNull('expires_at')->where('expires_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('is_published', false)->whereNull('published_at');
    }

    public function scopeUnpublished(Builder $query): Builder
    {
        return $query->where('is_published', false)->whereNotNull('published_at')->where(fn (Builder $q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public function scopeGlobal(Builder $query): Builder
    {
        return $query->where('applies_to_all_storefronts', true);
    }
}
