<?php

$rawFtpHost = env('FTP_HOST');
$parsedFtp  = $rawFtpHost ? @parse_url($rawFtpHost) : null;
$ftpScheme  = is_array($parsedFtp) ? ($parsedFtp['scheme'] ?? null) : null;
$ftpHost    = is_array($parsedFtp) ? ($parsedFtp['host'] ?? $rawFtpHost) : $rawFtpHost;
$ftpPort    = is_array($parsedFtp) && isset($parsedFtp['port']) ? (int) $parsedFtp['port'] : (int) env('FTP_PORT', 21);

$sslEnv = env('FTP_SSL');
$ftpSsl = $sslEnv !== null
    ? filter_var($sslEnv, FILTER_VALIDATE_BOOLEAN)
    : ($ftpScheme === 'ftps');

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DISK', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been set up for each driver as an example of the required values.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
            'throw' => false,
        ],

        // Public disk now points to FTP storage for uploads and public assets
        'public' => [
            'driver' => 'ftp',
            'host' => $ftpHost,
            'username' => env('FTP_USERNAME'),
            'password' => env('FTP_PASSWORD'),
            // Optional settings with sensible defaults
            'port' => $ftpPort,
            'root' => env('FTP_ROOT', '/'), // e.g. '/public_html/storage'
            'passive' => filter_var(env('FTP_PASSIVE', true), FILTER_VALIDATE_BOOLEAN),
            'ssl' => $ftpSsl,
            'timeout' => (int) env('FTP_TIMEOUT', 60), // Increased timeout
            'retries' => (int) env('FTP_RETRIES', 3), // Add retry mechanism
            // URL base for generating public URLs via Storage::url()
            'url' => env('FILES_PUBLIC_URL'), // e.g. https://2870351904.cloudydl.com
            'visibility' => 'public',
            'throw' => false,
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
            'use_path_style_endpoint' => env('AWS_USE_PATH_STYLE_ENDPOINT', false),
            'throw' => false,
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
