<?php

$s3AdapterAvailable = class_exists(\League\Flysystem\AwsS3V3\PortableVisibilityConverter::class);

// Detect Laravel Cloud injected disk configs if present.
$laravelCloudDisks = [];
$rawCloudConfig = env('LARAVEL_CLOUD_DISK_CONFIG', $_SERVER['LARAVEL_CLOUD_DISK_CONFIG'] ?? null);
if (is_string($rawCloudConfig) && trim($rawCloudConfig) !== '') {
    $decoded = json_decode($rawCloudConfig, true);
    if (is_array($decoded)) {
        $laravelCloudDisks = $decoded;
    }
}

// Check if any Laravel Cloud disk is marked as default
$cloudDefaultDisk = null;
foreach ($laravelCloudDisks as $cloudDisk) {
    if (! empty($cloudDisk['is_default']) && ! empty($cloudDisk['disk'])) {
        $cloudDefaultDisk = (string) $cloudDisk['disk'];
        break;
    }
}

$rawFilesystemDisk = env('FILESYSTEM_DISK');
$defaultDisk = ($cloudDefaultDisk && (! filled($rawFilesystemDisk) || $rawFilesystemDisk === 'local'))
    ? $cloudDefaultDisk
    : (string) env('FILESYSTEM_DISK', $cloudDefaultDisk ?: 'local');

$publicDiskDriver = strtolower((string) env('PUBLIC_DISK_DRIVER', in_array($defaultDisk, ['s3', $cloudDefaultDisk], true) ? 's3' : 'local'));
$useS3PublicDisk = ($publicDiskDriver === 's3' || $defaultDisk === 's3' || ($cloudDefaultDisk !== null && $defaultDisk === $cloudDefaultDisk)) && $s3AdapterAvailable;

if (in_array($defaultDisk, ['s3', 'public'], true) && ! $s3AdapterAvailable && $publicDiskDriver === 's3') {
    // Prevent hard failure when S3 is configured but the adapter package is missing.
    $defaultDisk = 'local';
}

$s3Key = env('AWS_ACCESS_KEY_ID');
$s3Secret = env('AWS_SECRET_ACCESS_KEY');
$s3Region = env('AWS_DEFAULT_REGION', env('AWS_REGION', 'auto')) ?: 'auto';
$s3Bucket = env('AWS_BUCKET');
$s3Url = env('AWS_URL');
$s3Endpoint = env('AWS_ENDPOINT', env('AWS_ENDPOINT_URL'));
$s3UsePathStyle = env('AWS_USE_PATH_STYLE_ENDPOINT', false);

$disks = [

    'local' => [
        'driver' => 'local',
        'root' => storage_path('app/private'),
        'serve' => true,
        'throw' => false,
        'report' => false,
    ],

    'public' => $useS3PublicDisk
        ? [
            'driver' => 's3',
            'key' => $s3Key,
            'secret' => $s3Secret,
            'region' => $s3Region,
            'bucket' => $s3Bucket,
            'url' => $s3Url,
            'endpoint' => $s3Endpoint,
            'use_path_style_endpoint' => (bool) $s3UsePathStyle,
            'throw' => false,
            'report' => false,
        ]
        : [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('PUBLIC_DISK_URL', '/storage'),
            'visibility' => 'public',
            'throw' => false,
            'report' => false,
        ],

    's3' => [
        'driver' => 's3',
        'key' => $s3Key,
        'secret' => $s3Secret,
        'region' => $s3Region,
        'bucket' => $s3Bucket,
        'url' => $s3Url,
        'endpoint' => $s3Endpoint,
        'use_path_style_endpoint' => (bool) $s3UsePathStyle,
        'throw' => false,
        'report' => false,
    ],

];

// Register dynamic disks from Laravel Cloud
foreach ($laravelCloudDisks as $cloudDisk) {
    if (empty($cloudDisk['disk'])) {
        continue;
    }
    $diskName = (string) $cloudDisk['disk'];
    $disks[$diskName] = [
        'driver' => 's3',
        'key' => $cloudDisk['access_key_id'] ?? $s3Key,
        'secret' => $cloudDisk['access_key_secret'] ?? $s3Secret,
        'region' => $cloudDisk['region'] ?? $s3Region,
        'bucket' => $cloudDisk['bucket'] ?? $s3Bucket,
        'url' => $cloudDisk['url'] ?? $s3Url,
        'endpoint' => $cloudDisk['endpoint'] ?? $s3Endpoint,
        'use_path_style_endpoint' => (bool) ($cloudDisk['use_path_style_endpoint'] ?? $s3UsePathStyle),
        'throw' => false,
        'report' => false,
    ];
}

if ($cloudDefaultDisk && isset($disks[$cloudDefaultDisk]) && $useS3PublicDisk && empty($s3Bucket)) {
    $disks['public'] = $disks[$cloudDefaultDisk];
}

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application for file storage.
    |
    */

    'default' => $defaultDisk,

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Below you may configure as many filesystem disks as necessary, and you
    | may even configure multiple disks for the same driver. Examples for
    | most supported drivers are configured here for reference.
    |
    | Supported drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => $disks,

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
