<?php

return [
    'enabled' => env('CACHE_METRICS_ENABLED', true),
    'service_name' => env('CACHE_METRICS_SERVICE', 'admin_service'),
    'redis_connection' => env('CACHE_METRICS_REDIS_CONNECTION', env('REDIS_CACHE_CONNECTION', 'cache')),
    'key_prefix' => env('CACHE_METRICS_KEY_PREFIX', 'glamrush:metrics:cache'),
    'bucket_minutes' => max(1, (int) env('CACHE_METRICS_BUCKET_MINUTES', 5)),
    'raw_ttl_seconds' => max(3600, (int) env('CACHE_METRICS_RAW_TTL_SECONDS', 172800)),
    'aggregation_schedule' => env('CACHE_METRICS_AGGREGATION_SCHEDULE', '*/5 * * * *'),
    'flush' => [
        'enabled' => env('CACHE_METRICS_FLUSH_ENABLED', true),
        'exclude_metrics' => env('CACHE_METRICS_FLUSH_EXCLUDE_METRICS', true),
        'service_prefixes' => [
            'admin_service' => env('CACHE_METRICS_ADMIN_CACHE_PREFIX'),
            'backend_service' => env('CACHE_METRICS_BACKEND_CACHE_PREFIX'),
        ],
    ],
];
