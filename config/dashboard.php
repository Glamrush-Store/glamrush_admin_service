<?php

return [
    'cache' => [
        'store' => env('DASHBOARD_ANALYTICS_CACHE_STORE', 'redis'),
        'ttl_seconds' => (int) env('DASHBOARD_ANALYTICS_CACHE_TTL', 300),
    ],

    'aggregation' => [
        'schedule' => env('DASHBOARD_ANALYTICS_SCHEDULE', '*/30 * * * *'),
        'periods' => array_values(array_filter(explode(',', env('DASHBOARD_ANALYTICS_PERIODS', 'week,month,quarter,year')))),
    ],

    'stock' => [
        'low_stock_threshold' => (int) env('DASHBOARD_LOW_STOCK_THRESHOLD', 2),
    ],

    'limits' => [
        'recent_orders' => (int) env('DASHBOARD_RECENT_ORDERS_LIMIT', 10),
        'products' => (int) env('DASHBOARD_PRODUCTS_LIMIT', 10),
        'stock_alerts' => (int) env('DASHBOARD_STOCK_ALERTS_LIMIT', 20),
    ],

    'sales_statuses' => [
        'paid',
        'processing',
        'shipped',
        'completed',
    ],

    'completed_status' => 'completed',
];
