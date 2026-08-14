<?php

namespace App\Console\Commands;

use App\Models\CacheMetricSnapshot;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Throwable;

class AggregateCacheMetricsCommand extends Command
{
    protected $signature = 'cache-metrics:aggregate {--delete-raw : Delete raw Redis metric buckets after storing snapshots}';

    protected $description = 'Aggregate per-service Redis cache metric counters into dashboard snapshots.';

    public function handle(): int
    {
        $prefix = trim((string) config('cache_metrics.key_prefix', 'glamrush:metrics:cache'), ':');
        $bucketMinutes = max(1, (int) config('cache_metrics.bucket_minutes', 5));
        $redisMetrics = $this->redisServerMetrics();
        $stored = 0;

        foreach ($this->scanKeys('*'.$prefix.':*') as $key) {
            $prefixPosition = strpos($key, $prefix);
            if ($prefixPosition === false) {
                continue;
            }

            $logicalKey = substr($key, $prefixPosition);
            $parts = explode(':', $logicalKey);
            $count = count($parts);
            if ($count < 6) {
                continue;
            }

            $bucket = $parts[$count - 1];
            $area = $parts[$count - 2];
            $service = $parts[$count - 3];

            $fields = $this->hash($key);
            $hits = (int) ($fields['hits'] ?? 0);
            $misses = (int) ($fields['misses'] ?? 0);
            $writes = (int) ($fields['writes'] ?? 0);
            $forgets = (int) ($fields['forgets'] ?? 0);

            if ($hits + $misses + $writes + $forgets === 0) {
                continue;
            }

            try {
                $bucketStart = Carbon::createFromFormat('YmdHi', $bucket, config('app.timezone'));
            } catch (Throwable) {
                continue;
            }

            $reads = $hits + $misses;
            $hitRatio = $reads > 0 ? round(($hits / $reads) * 100, 4) : 0;

            CacheMetricSnapshot::query()->updateOrCreate(
                [
                    'service_name' => $service,
                    'area' => $area,
                    'bucket_started_at' => $bucketStart,
                ],
                [
                    'bucket_ended_at' => $bucketStart->copy()->addMinutes($bucketMinutes),
                    'hits' => $hits,
                    'misses' => $misses,
                    'writes' => $writes,
                    'forgets' => $forgets,
                    'hit_ratio' => $hitRatio,
                    'redis_metrics' => $redisMetrics,
                ]
            );

            $stored++;

            if ($this->option('delete-raw')) {
                Redis::connection(config('cache_metrics.redis_connection'))->client()->rawCommand('DEL', $key);
            }
        }

        Cache::put('cache-metrics:last-aggregation-at', now()->toISOString(), now()->addDay());
        $this->info("Aggregated {$stored} cache metric bucket(s).");

        return self::SUCCESS;
    }

    /** @return list<string> */
    private function scanKeys(string $pattern): array
    {
        $redis = Redis::connection(config('cache_metrics.redis_connection'));
        $cursor = '0';
        $keys = [];

        do {
            $result = $redis->client()->rawCommand('SCAN', $cursor, 'MATCH', $pattern, 'COUNT', 500);
            $cursor = (string) ($result[0] ?? '0');
            $batch = $result[1] ?? [];
            foreach ($batch as $key) {
                $keys[] = (string) $key;
            }
        } while ($cursor !== '0');

        return $keys;
    }

    /** @return array<string, mixed> */
    private function hash(string $key): array
    {
        $fields = Redis::connection(config('cache_metrics.redis_connection'))->client()->rawCommand('HGETALL', $key);

        if (! is_array($fields)) {
            return [];
        }

        if (array_is_list($fields)) {
            $normalized = [];
            for ($i = 0; $i < count($fields); $i += 2) {
                if (isset($fields[$i], $fields[$i + 1])) {
                    $normalized[(string) $fields[$i]] = $fields[$i + 1];
                }
            }

            return $normalized;
        }

        return $fields;
    }

    private function redisKeyPrefix(): string
    {
        $client = Redis::connection(config('cache_metrics.redis_connection'))->client();

        if (! method_exists($client, 'getOption') || ! class_exists(\Redis::class)) {
            return '';
        }

        $prefix = $client->getOption(\Redis::OPT_PREFIX);

        return is_string($prefix) ? $prefix : '';
    }

    /** @return array<string, mixed>|null */
    private function redisServerMetrics(): ?array
    {
        try {
            $info = Redis::connection(config('cache_metrics.redis_connection'))->command('info');
        } catch (Throwable) {
            return null;
        }

        $parsed = is_array($info) ? $info : $this->parseRedisInfoString($info);

        return array_intersect_key($parsed, array_flip([
            'used_memory',
            'used_memory_human',
            'used_memory_peak_human',
            'used_memory_peak',
            'connected_clients',
            'blocked_clients',
            'keyspace_hits',
            'keyspace_misses',
            'expired_keys',
            'evicted_keys',
            'total_commands_processed',
            'instantaneous_ops_per_sec',
        ]));
    }

    /** @return array<string, mixed> */
    private function parseRedisInfoString(mixed $info): array
    {
        if (! is_string($info)) {
            return [];
        }

        $parsed = [];
        foreach (explode("\n", $info) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, ':')) {
                continue;
            }
            [$key, $value] = explode(':', $line, 2);
            $parsed[$key] = is_numeric($value) ? $value + 0 : $value;
        }

        return $parsed;
    }
}
