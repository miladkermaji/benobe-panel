<?php

return [
    /*
    |--------------------------------------------------------------------------
    | CDN Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration for CDN services and image fallbacks
    |
    */

    'default' => env('CDN_DEFAULT', 'local'),

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL') . '/storage',
        ],

        'ftp' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            'port' => env('FTP_PORT', 21),
            'root' => env('FTP_ROOT', '/'),
            'passive' => env('FTP_PASSIVE', true),
            'ssl' => env('FTP_SSL', false),
            'timeout' => env('FTP_TIMEOUT', 60),
            'retries' => env('FTP_RETRIES', 3),
            'url' => env('FILES_PUBLIC_URL'),
            'visibility' => 'public',
        ],

        'cloudydl' => [
            'driver' => 'ftp',
            'host' => env('FTP_HOST'),
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            'port' => env('FTP_PORT', 21),
            'root' => env('FTP_ROOT', '/'),
            'passive' => env('FTP_PASSIVE', true),
            'ssl' => env('FTP_SSL', false),
            'timeout' => env('FTP_TIMEOUT', 60),
            'retries' => env('FTP_RETRIES', 3),
            'url' => env('FILES_PUBLIC_URL'),
            'visibility' => 'public',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Fallback Configuration
    |--------------------------------------------------------------------------
    |
    | Default images to use when CDN images fail to load
    |
    */

    'fallbacks' => [
        'admin' => [
            'profile' => 'admin-assets/panel/img/pro.jpg',
            'avatar' => 'admin-assets/panel/img/pro.jpg',
        ],
        'doctor' => [
            'profile' => 'dr-assets/panel/img/pro.jpg',
            'avatar' => 'dr-assets/panel/img/pro.jpg',
        ],
        'medical_center' => [
            'profile' => 'mc-assets/panel/img/pro.jpg',
            'avatar' => 'mc-assets/panel/img/pro.jpg',
        ],
        'default' => [
            'profile' => 'dr-assets/panel/img/pro.jpg',
            'avatar' => 'dr-assets/panel/img/pro.jpg',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CDN Health Check Configuration
    |--------------------------------------------------------------------------
    |
    | Settings for monitoring CDN health and availability
    |
    */

    'health_check' => [
        'enabled' => env('CDN_HEALTH_CHECK_ENABLED', true),
        'timeout' => env('CDN_HEALTH_CHECK_TIMEOUT', 5),
        'retries' => env('CDN_HEALTH_CHECK_RETRIES', 3),
        'cache_duration' => env('CDN_HEALTH_CHECK_CACHE', 300), // 5 minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for image optimization and caching
    |
    */

    'optimization' => [
        'enabled' => env('CDN_OPTIMIZATION_ENABLED', false),
        'quality' => env('CDN_IMAGE_QUALITY', 85),
        'format' => env('CDN_IMAGE_FORMAT', 'webp'),
        'cache_headers' => [
            'Cache-Control' => 'public, max-age=31536000', // 1 year
            'Expires' => '+1 year',
        ],
    ],
];
