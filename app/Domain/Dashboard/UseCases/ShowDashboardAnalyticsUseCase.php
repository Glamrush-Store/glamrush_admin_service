<?php

namespace App\Domain\Dashboard\UseCases;

use App\Domain\Dashboard\Actions\BuildDashboardAnalyticsSnapshotAction;
use App\Models\DashboardAnalyticsSnapshot;
use Illuminate\Support\Facades\Cache;
use Throwable;

class ShowDashboardAnalyticsUseCase
{
    public function __construct(private BuildDashboardAnalyticsSnapshotAction $buildSnapshot) {}

    public function run(array $filters): array
    {
        $period = $filters['period'] ?? 'week';
        $from = $filters['from'] ?? null;
        $to = $filters['to'] ?? null;

        $cacheKey = implode(':', array_filter([
            'dashboard',
            'analytics',
            $period,
            $from ? md5((string) $from) : null,
            $to ? md5((string) $to) : null,
        ]));

        $ttl = now()->addSeconds((int) config('dashboard.cache.ttl_seconds', 300));

        try {
            return Cache::store(config('dashboard.cache.store', 'redis'))->remember(
                $cacheKey,
                $ttl,
                fn () => $this->resolvePayload($period, $from, $to)
            );
        } catch (Throwable) {
            return Cache::remember(
                $cacheKey,
                $ttl,
                fn () => $this->resolvePayload($period, $from, $to)
            );
        }
    }

    private function resolvePayload(string $period, ?string $from, ?string $to): array
    {
        [$startsAt, $endsAt, $periodKey] = $this->buildSnapshot->resolveRange($period, $from, $to);

        $snapshot = DashboardAnalyticsSnapshot::query()
            ->where('period', $periodKey)
            ->where('starts_at', $startsAt)
            ->where('ends_at', $endsAt)
            ->latest('aggregated_at')
            ->first();

        if ($snapshot === null) {
            $snapshot = $this->buildSnapshot->run($period, $from, $to);
        }

        return array_merge($snapshot->payload, [
            'snapshot' => [
                'id' => $snapshot->id,
                'aggregated_at' => $snapshot->aggregated_at?->toIso8601String(),
                'response_cached_for_seconds' => (int) config('dashboard.cache.ttl_seconds', 300),
            ],
        ]);
    }
}
