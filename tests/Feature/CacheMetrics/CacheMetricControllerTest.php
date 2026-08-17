<?php

use App\Models\CacheMetricSnapshot;
use App\Models\User;
use App\Infrastructure\CacheMetrics\CacheFlushService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

function cacheMetricsAdmin(array $permissions): User
{
    $user = User::factory()->create();

    foreach ($permissions as $permission) {
        Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'sanctum']);
    }

    $user->givePermissionTo($permissions);
    Sanctum::actingAs($user);

    return $user;
}

it('requires dashboard permission to view cache metrics', function () {
    $this->getJson('/api/v1/cache-metrics')->assertUnauthorized();

    Sanctum::actingAs(User::factory()->create());

    $this->getJson('/api/v1/cache-metrics')->assertForbidden();
    $this->getJson('/api/v1/cache-metrics/status')->assertForbidden();
});

it('lists cache metric summaries and time series for both services', function () {
    Cache::put('cache-metrics:last-aggregation-at', '2026-08-14T10:05:00.000000Z');
    cacheMetricsAdmin(['View_Dashboard']);

    $bucket = Carbon::parse('2026-08-14 10:00:00');

    CacheMetricSnapshot::query()->create([
        'service_name' => 'backend_service',
        'area' => 'catalog',
        'bucket_started_at' => $bucket,
        'bucket_ended_at' => $bucket->copy()->addMinutes(5),
        'hits' => 90,
        'misses' => 10,
        'writes' => 4,
        'forgets' => 1,
        'hit_ratio' => 90,
        'redis_metrics' => ['used_memory_human' => '2M', 'connected_clients' => 3],
    ]);

    CacheMetricSnapshot::query()->create([
        'service_name' => 'admin_service',
        'area' => 'settings',
        'bucket_started_at' => $bucket->copy()->addMinutes(5),
        'bucket_ended_at' => $bucket->copy()->addMinutes(10),
        'hits' => 5,
        'misses' => 5,
        'writes' => 2,
        'forgets' => 0,
        'hit_ratio' => 50,
        'redis_metrics' => ['used_memory_human' => '3M', 'connected_clients' => 4],
    ]);

    $this->getJson('/api/v1/cache-metrics?from=2026-08-14T09:55:00Z&to=2026-08-14T10:15:00Z')
        ->assertOk()
        ->assertJsonPath('data.summary.hits', 95)
        ->assertJsonPath('data.summary.misses', 15)
        ->assertJsonPath('data.summary.writes', 6)
        ->assertJsonPath('data.summary.forgets', 1)
        ->assertJsonPath('data.summary.hit_ratio', 86.3636)
        ->assertJsonPath('data.summary.last_aggregation_at', '2026-08-14T10:05:00.000000Z')
        ->assertJsonPath('data.meta.total', 2)
        ->assertJsonCount(2, 'data.summary.by_service_area')
        ->assertJsonCount(2, 'data.series');
});

it('filters cache metric series by service and area', function () {
    cacheMetricsAdmin(['View_Dashboard']);
    $bucket = Carbon::parse('2026-08-14 10:00:00');

    CacheMetricSnapshot::query()->create([
        'service_name' => 'backend_service',
        'area' => 'catalog',
        'bucket_started_at' => $bucket,
        'bucket_ended_at' => $bucket->copy()->addMinutes(5),
        'hits' => 12,
        'misses' => 3,
        'writes' => 1,
        'forgets' => 0,
        'hit_ratio' => 80,
    ]);

    CacheMetricSnapshot::query()->create([
        'service_name' => 'backend_service',
        'area' => 'search',
        'bucket_started_at' => $bucket,
        'bucket_ended_at' => $bucket->copy()->addMinutes(5),
        'hits' => 2,
        'misses' => 8,
        'writes' => 0,
        'forgets' => 0,
        'hit_ratio' => 20,
    ]);

    $this->getJson('/api/v1/cache-metrics?service=backend_service&area=catalog&from=2026-08-14T09:55:00Z&to=2026-08-14T10:10:00Z')
        ->assertOk()
        ->assertJsonPath('data.filters.service', 'backend_service')
        ->assertJsonPath('data.filters.area', 'catalog')
        ->assertJsonPath('data.meta.total', 1)
        ->assertJsonPath('data.series.0.service_name', 'backend_service')
        ->assertJsonPath('data.series.0.area', 'catalog')
        ->assertJsonPath('data.summary.by_service_area.0.hit_ratio', 80);
});

it('returns cache metric service status', function () {
    Cache::put('cache-metrics:last-aggregation-at', '2026-08-14T10:05:00.000000Z');
    cacheMetricsAdmin(['View_Dashboard']);
    $bucket = Carbon::parse('2026-08-14 10:00:00');

    CacheMetricSnapshot::query()->create([
        'service_name' => 'backend_service',
        'area' => 'catalog',
        'bucket_started_at' => $bucket,
        'bucket_ended_at' => $bucket->copy()->addMinutes(5),
        'hits' => 1,
        'misses' => 0,
        'writes' => 0,
        'forgets' => 0,
        'hit_ratio' => 100,
        'redis_metrics' => ['used_memory_human' => '2M'],
    ]);

    $this->getJson('/api/v1/cache-metrics/status')
        ->assertOk()
        ->assertJsonPath('data.last_aggregation_at', '2026-08-14T10:05:00.000000Z')
        ->assertJsonPath('data.redis.used_memory_human', '2M')
        ->assertJsonPath('data.services.0.service_name', 'backend_service');
});

it('refreshes cache metrics on demand', function () {
    Cache::put('cache-metrics:last-aggregation-at', '2026-08-14T10:05:00.000000Z');
    cacheMetricsAdmin(['View_Dashboard']);

    Artisan::shouldReceive('call')
        ->once()
        ->with('cache-metrics:aggregate')
        ->andReturn(0);
    Artisan::shouldReceive('output')
        ->once()
        ->andReturn("Aggregated 2 cache metric bucket(s).\n");

    $this->postJson('/api/v1/cache-metrics/refresh')
        ->assertOk()
        ->assertJsonPath('message', 'Cache metrics refreshed')
        ->assertJsonPath('data.output', 'Aggregated 2 cache metric bucket(s).')
        ->assertJsonPath('data.last_aggregation_at', '2026-08-14T10:05:00.000000Z');
});

it('requires update dashboard permission and confirmation to flush cache', function () {
    $this->postJson('/api/v1/cache-metrics/flush', [
        'service' => 'backend_service',
        'confirm' => true,
    ])->assertUnauthorized();

    cacheMetricsAdmin(['View_Dashboard']);

    $this->postJson('/api/v1/cache-metrics/flush', [
        'service' => 'backend_service',
        'confirm' => true,
    ])->assertForbidden();

    cacheMetricsAdmin(['Update_Dashboard']);

    $this->postJson('/api/v1/cache-metrics/flush', [
        'service' => 'backend_service',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('confirm');
});

it('flushes cache for the selected service', function () {
    Cache::put('cache-metrics:last-aggregation-at', '2026-08-14T10:05:00.000000Z');
    cacheMetricsAdmin(['Update_Dashboard']);

    $this->mock(CacheFlushService::class)
        ->shouldReceive('flush')
        ->once()
        ->with('backend_service', false)
        ->andReturn([
            'service' => 'backend_service',
            'deleted' => 12,
            'prefixes' => ['glamrush-stores-database-'],
        ]);

    Artisan::shouldReceive('call')
        ->once()
        ->with('cache-metrics:aggregate')
        ->andReturn(0);

    $this->postJson('/api/v1/cache-metrics/flush', [
        'service' => 'backend_service',
        'confirm' => true,
    ])
        ->assertOk()
        ->assertJsonPath('message', 'Cache flushed')
        ->assertJsonPath('data.service', 'backend_service')
        ->assertJsonPath('data.deleted', 12)
        ->assertJsonPath('data.prefixes.0', 'glamrush-stores-database-')
        ->assertJsonPath('data.last_aggregation_at', '2026-08-14T10:05:00.000000Z');
});
