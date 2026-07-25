<?php

$configuredUploadDisk = (string) env('VIDEO_UPLOAD_DISK', 'public');
$s3AdapterAvailable = class_exists(\League\Flysystem\AwsS3V3\PortableVisibilityConverter::class);

if ($configuredUploadDisk === 's3' && ! $s3AdapterAvailable) {
    $configuredUploadDisk = 'public';
}

return [
    /*
    |--------------------------------------------------------------------------
    | Video upload disk
    |--------------------------------------------------------------------------
    | Filesystem disk that direct video uploads (ResourceVideo local files)
    | are stored on. Defaults to the public disk in dev; set
    | VIDEO_UPLOAD_DISK=s3 to move uploads to S3 without code changes.
    */
    'upload_disk' => $configuredUploadDisk,

    /*
    |--------------------------------------------------------------------------
    | FFmpeg binary
    |--------------------------------------------------------------------------
    | Path to (or name of) the FFmpeg executable used by the TranscodeVideo
    | job. When the binary cannot be executed, transcoding degrades
    | gracefully: uploads are kept and served as-is with status 'skipped'.
    */
    'ffmpeg_path' => env('FFMPEG_PATH', 'ffmpeg'),
];
