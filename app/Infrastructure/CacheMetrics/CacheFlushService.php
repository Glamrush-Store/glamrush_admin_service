<?php

namespace App\Infrastructure\CacheMetrics;

use RuntimeException;
use Illuminate\Support\Facades\Redis;

class CacheFlushService
{
    /** @return array{service:string, deleted:int, prefixes:array<int, string>} */
    public function flush(string $service, bool $includeMetrics = false): array
    {
        if (! config('cache_metrics.flush.enabled', true)) {
            throw new RuntimeException('Cache flushing is disabled.');
        }

        $services = $service === 'all' ? ['admin_service', 'backend_service'] : [$service];
        $deleted = 0;
        $prefixes = [];

        foreach ($services as $serviceName) {
            foreach ($this->prefixesFor($serviceName) as $prefix) {
                $prefixes[] = $prefix;
                $deleted += $this->deleteByPrefix($prefix, $includeMetrics);
            }
        }

        return [
            'service' => $service,
            'deleted' => $deleted,
            'prefixes' => array_values(array_unique($prefixes)),
        ];
    }

    /** @return list<string> */
    private function prefixesFor(string $service): array
    {
        $configured = config("cache_metrics.flush.service_prefixes.{$service}");

        if (is_string($configured) && trim($configured) !== '') {
            return [trim($configured)];
        }

        if ($service === config('cache_metrics.service_name', 'admin_service')) {
            $prefix = config('database.redis.options.prefix');

            if (is_string($prefix) && trim($prefix) !== '') {
                return [trim($prefix)];
            }
        }

        throw new RuntimeException("Cache prefix is not configured for {$service}.");
    }

    private function deleteByPrefix(string $prefix, bool $includeMetrics): int
    {
        if ($prefix === '' || str_contains($prefix, '*')) {
            throw new RuntimeException('Refusing to flush cache with an unsafe Redis prefix.');
        }

        $metricsPrefix = trim((string) config('cache_metrics.key_prefix', 'glamrush:metrics:cache'), ':');
        $redis = Redis::connection(config('cache_metrics.redis_connection'));
        $cursor = '0';
        $deleted = 0;

        do {
            $result = $redis->client()->rawCommand('SCAN', $cursor, 'MATCH', $prefix.'*', 'COUNT', 500);
            $cursor = (string) ($result[0] ?? '0');
            $keys = array_values(array_filter(array_map('strval', $result[1] ?? []), function (string $key) use ($includeMetrics, $metricsPrefix): bool {
                return $includeMetrics || ! str_contains($key, $metricsPrefix);
            }));

            foreach (array_chunk($keys, 100) as $chunk) {
                if ($chunk === []) {
                    continue;
                }

                $deleted += (int) $redis->client()->rawCommand('DEL', ...$chunk);
            }
        } while ($cursor !== '0');

        return $deleted;
    }
}
