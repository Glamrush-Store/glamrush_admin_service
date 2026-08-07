<?php

namespace App\Models;

use App\Domain\Newsletter\Enums\NewsletterSubscriberStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class NewsletterSubscriber extends Model
{
    use HasUlids;

    protected $table = 'newsletter_subscribers';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = ['*'];

    protected $hidden = [
        'confirmation_token_hash',
        'unsubscribe_token_hash',
        'confirmation_expires_at',
        'consent_ip_hash',
        'consent_user_agent',
    ];

    protected function casts(): array
    {
        return [
            'status' => NewsletterSubscriberStatus::class,
            'confirmation_expires_at' => 'immutable_datetime',
            'consented_at' => 'immutable_datetime',
            'confirmed_at' => 'immutable_datetime',
            'unsubscribed_at' => 'immutable_datetime',
        ];
    }

    public function scopeWithStatus(Builder $query, ?string $status): Builder
    {
        return $query->when($status, fn (Builder $query) => $query->where('status', $status));
    }

    public function scopeFromSource(Builder $query, ?string $source): Builder
    {
        return $query->when($source, fn (Builder $query) => $query->where('source', $source));
    }

    public function scopeSearchEmail(Builder $query, ?string $email): Builder
    {
        return $query->when($email, fn (Builder $query) => $query->where('email', 'like', '%'.$email.'%'));
    }

    public function scopeConfirmedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $query) => $query->where('confirmed_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->where('confirmed_at', '<=', $this->inclusiveEndDate($to)));
    }

    public function scopeCreatedBetween(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $query) => $query->where('created_at', '>=', $from))
            ->when($to, fn (Builder $query) => $query->where('created_at', '<=', $this->inclusiveEndDate($to)));
    }

    private function inclusiveEndDate(string $value): string
    {
        return preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)
            ? Carbon::parse($value)->endOfDay()->toDateTimeString()
            : $value;
    }
}
