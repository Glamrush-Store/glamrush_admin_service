<?php

return [
    'disk' => env('PRODUCT_IMPORT_DISK', 'local'),
    'queue' => env('PRODUCT_IMPORT_QUEUE', 'default'),
    'max_file_size_kb' => (int) env('PRODUCT_IMPORT_MAX_FILE_SIZE_KB', 10240),
    'max_rows' => (int) env('PRODUCT_IMPORT_MAX_ROWS', 5000),
    'max_product_images' => (int) env('PRODUCT_IMPORT_MAX_PRODUCT_IMAGES', 8),
    'max_variant_images' => (int) env('PRODUCT_IMPORT_MAX_VARIANT_IMAGES', 2),
    'max_image_size_bytes' => (int) env('PRODUCT_IMPORT_MAX_IMAGE_SIZE_BYTES', 5242880),
    'image_connect_timeout_seconds' => (int) env('PRODUCT_IMPORT_IMAGE_CONNECT_TIMEOUT', 5),
    'image_timeout_seconds' => (int) env('PRODUCT_IMPORT_IMAGE_TIMEOUT', 15),
    'image_max_redirects' => (int) env('PRODUCT_IMPORT_IMAGE_MAX_REDIRECTS', 3),
];
