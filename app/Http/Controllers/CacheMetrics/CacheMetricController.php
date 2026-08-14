<?php

namespace App\Http\Controllers\CacheMetrics;

use App\Http\Requests\CacheMetrics\FlushCacheRequest;
use App\Http\Requests\CacheMetrics\ListCacheMetricsRequest;
use App\Http\Responses\ApiResponse;
use App\Infrastructure\CacheMetrics\CacheFlushService;
use App\Models\CacheMetricSnapshot;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use RuntimeException;

class CacheMetricController
{
    public function index(ListCacheMetricsRequest $request): JsonResponse
    {
        $data = $request->validated();
        $from = isset($data['from']) ? Carbon::parse($data['from']) : now()->subDay();
        $to = isset($data['to']) ? Carbon::parse($data['to']) : now();
        $sortDir = $data['sort_dir'] ?? 'asc';

        $query = CacheMetricSnapshot::query()
            ->whereBetween('bucket_started_at', [$from, $to])
            ->when($data['service'] ?? null, fn ($query, $service) => $query->where('service_name', $service))
            ->when($data['area'] ?? null, fn ($query, $area) => $query->where('area', $area));

        $summaryRows = (clone $query)
            ->selectRaw('service_name, area, SUM(hits) as hits, SUM(misses) as misses, SUM(writes) as writes, SUM(forgets) as forgets')
            ->groupBy('service_name', 'area')
            ->get();

        $totalHits = (int) $summaryRows->sum('hits');
        $totalMisses = (int) $summaryRows->sum('misses');
        $totalReads = $totalHits + $totalMisses;

        $series = (clone $query)
            ->orderBy('bucket_started_at', $sortDir)
            ->orderBy('service_name')
            ->orderBy('area')
            ->paginate($data['per_page'] ?? 250);

        $latestRedisMetrics = $this->latestRedisMetrics();

        return ApiResponse::success([
            'filters' => [
                'service' => $data['service'] ?? null,
                'area' => $data['area'] ?? null,
                'from' => $from->toISOString(),
                'to' => $to->toISOString(),
            ],
            'summary' => [
                'hits' => $totalHits,
                'misses' => $totalMisses,
                'writes' => (int) $summaryRows->sum('writes'),
                'forgets' => (int) $summaryRows->sum('forgets'),
                'hit_ratio' => $totalReads > 0 ? round(($totalHits / $totalReads) * 100, 4) : 0,
                'by_service_area' => $summaryRows->map(fn ($row) => $this->summaryRow($row))->values(),
                'redis' => $latestRedisMetrics,
                'last_aggregation_at' => Cache::get('cache-metrics:last-aggregation-at'),
            ],
            'series' => $series->getCollection()->map(fn (CacheMetricSnapshot $snapshot) => $this->seriesRow($snapshot))->values(),
            'meta' => [
                'current_page' => $series->currentPage(),
                'last_page' => $series->lastPage(),
                'per_page' => $series->perPage(),
                'total' => $series->total(),
            ],
        ]);
    }

    public function status(): JsonResponse
    {
        $latestByService = CacheMetricSnapshot::query()
            ->selectRaw('service_name, MAX(bucket_started_at) as last_metric_at')
            ->groupBy('service_name')
            ->get()
            ->map(fn ($row) => [
                'service_name' => $row->service_name,
                'last_metric_at' => Carbon::parse($row->last_metric_at)->toISOString(),
            ])
            ->values();

        $latest = CacheMetricSnapshot::query()->latest('bucket_started_at')->first();

        return ApiResponse::success([
            'last_aggregation_at' => Cache::get('cache-metrics:last-aggregation-at'),
            'latest_metric_at' => $latest?->bucket_started_at?->toISOString(),
            'redis' => $this->latestRedisMetrics(),
            'services' => $latestByService,
        ]);
    }

    public function refresh(): JsonResponse
    {
        Artisan::call('cache-metrics:aggregate');

        return ApiResponse::success([
            'output' => trim(Artisan::output()),
            'last_aggregation_at' => Cache::get('cache-metrics:last-aggregation-at'),
        ], 'Cache metrics refreshed');
    }

    public function flush(FlushCacheRequest $request, CacheFlushService $service): JsonResponse
    {
        $data = $request->validated();

        try {
            $result = $service->flush($data['service'], (bool) ($data['include_metrics'] ?? false));
        } catch (RuntimeException $exception) {
            return ApiResponse::error($exception->getMessage(), [], 422);
        }

        Artisan::call('cache-metrics:aggregate');

        return ApiResponse::success([
            ...$result,
            'last_aggregation_at' => Cache::get('cache-metrics:last-aggregation-at'),
        ], 'Cache flushed');
    }

    private function summaryRow(object $row): array
    {
        $hits = (int) $row->hits;
        $misses = (int) $row->misses;
        $reads = $hits + $misses;

        return [
            'service_name' => $row->service_name,
            'area' => $row->area,
            'hits' => $hits,
            'misses' => $misses,
            'writes' => (int) $row->writes,
            'forgets' => (int) $row->forgets,
            'hit_ratio' => $reads > 0 ? round(($hits / $reads) * 100, 4) : 0,
        ];
    }

    private function seriesRow(CacheMetricSnapshot $snapshot): array
    {
        return [
            'timestamp' => $snapshot->bucket_started_at?->toISOString(),
            'bucket_ended_at' => $snapshot->bucket_ended_at?->toISOString(),
            'service_name' => $snapshot->service_name,
            'area' => $snapshot->area,
            'hits' => $snapshot->hits,
            'misses' => $snapshot->misses,
            'writes' => $snapshot->writes,
            'forgets' => $snapshot->forgets,
            'hit_ratio' => $snapshot->hit_ratio,
        ];
    }

    private function latestRedisMetrics(): ?array
    {
        return CacheMetricSnapshot::query()
            ->whereNotNull('redis_metrics')
            ->latest('bucket_started_at')
            ->value('redis_metrics');
    }
}
