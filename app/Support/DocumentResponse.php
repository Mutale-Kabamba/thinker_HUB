<?php

namespace App\Support;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentResponse
{
    /**
     * Map common file extensions to MIME types.
     */
    protected static array $mimeTypes = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'dot' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dotx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'xls' => 'application/vnd.ms-excel',
        'xlt' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xltx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'ppt' => 'application/vnd.ms-powerpoint',
        'pot' => 'application/vnd.ms-powerpoint',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'potx' => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'webp' => 'image/webp',
        'svg' => 'image/svg+xml',
        'bmp' => 'image/bmp',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'txt' => 'text/plain',
        'csv' => 'text/csv',
        'json' => 'application/json',
        'py' => 'text/x-python',
        'ipynb' => 'application/x-ipynb+json',
        'sql' => 'application/sql',
        'md' => 'text/markdown',
        'js' => 'text/javascript',
        'ts' => 'text/plain',
        'html' => 'text/html',
        'css' => 'text/css',
        'zip' => 'application/zip',
        'rar' => 'application/vnd.rar',
        '7z' => 'application/x-7z-compressed',
        'tar' => 'application/x-tar',
        'gz' => 'application/gzip',
    ];

    /**
     * Detect MIME type from file extension or path.
     */
    public static function detectMimeType(?string $path, ?string $fallback = 'application/octet-stream'): string
    {
        if (! $path) {
            return $fallback ?? 'application/octet-stream';
        }

        $extension = strtolower((string) pathinfo($path, PATHINFO_EXTENSION));

        return static::$mimeTypes[$extension] ?? ($fallback ?? 'application/octet-stream');
    }

    /**
     * Resolve a file across configured candidate disks and path fallbacks.
     *
     * @return array{0: ?Filesystem, 1: ?string, 2: ?string, 3: ?string} [diskInstance, resolvedPath, diskName, absoluteLocalPath]
     */
    public static function resolveFile(?string $rawPath, ?string $preferredDisk = null): array
    {
        // Direct check if raw path is an existing absolute local file.
        if (file_exists($rawPath) && is_file($rawPath)) {
            $real = realpath($rawPath) ?: $rawPath;
            return [Storage::disk('local'), basename($rawPath), 'local', $real];
        }

        $normalized = PublicDiskPath::normalize($rawPath);

        if (! $normalized) {
            return [null, null, null, null];
        }

        // Generate candidate path variations.
        $candidatePaths = [$normalized];
        $baseName = basename($normalized);

        $variations = [
            $normalized,
            $baseName,
            urldecode($normalized),
            urldecode($baseName),
            str_replace(' ', '_', $normalized),
            str_replace(' ', '_', $baseName),
            str_replace('_', ' ', $normalized),
            str_replace('_', ' ', $baseName),
        ];

        foreach ($variations as $var) {
            if ($var !== '' && ! in_array($var, $candidatePaths, true)) {
                $candidatePaths[] = $var;
            }
        }

        $folderPrefixes = [
            'materials/',
            'learning-materials/',
            'assignments/',
            'assessments/',
            'submissions/',
            'assignment-submissions/',
            'instructor-cvs/',
            'instructor-roadmaps/',
            'instructor-curricula/',
            'chat-attachments/',
            'broadcasts/',
            'claim-items/',
            'hub-media/',
            'course-images/',
            'profile-photos/',
            'attachments/',
            'lesson-attachments/',
            'session-readings/',
            'session-videos/',
            'courses/',
            'temp/',
            'imports/',
            'livewire-tmp/',
        ];

        foreach ($folderPrefixes as $prefix) {
            foreach ([$baseName, str_replace(' ', '_', $baseName), str_replace('_', ' ', $baseName)] as $bn) {
                $candidate = $prefix . $bn;
                if (! in_array($candidate, $candidatePaths, true)) {
                    $candidatePaths[] = $candidate;
                }
            }
        }

        // Build list of candidate disks.
        $candidateDisks = [];
        if ($preferredDisk && config("filesystems.disks.{$preferredDisk}")) {
            $candidateDisks[] = $preferredDisk;
        }

        $candidateDisks[] = 'public';

        $defaultDisk = (string) config('filesystems.default', 'local');
        if (! in_array($defaultDisk, $candidateDisks, true) && config("filesystems.disks.{$defaultDisk}")) {
            $candidateDisks[] = $defaultDisk;
        }

        if (config('filesystems.disks.s3') && ! in_array('s3', $candidateDisks, true)) {
            $candidateDisks[] = 's3';
        }

        if (config('filesystems.disks.local') && ! in_array('local', $candidateDisks, true)) {
            $candidateDisks[] = 'local';
        }

        foreach (array_keys((array) config('filesystems.disks', [])) as $diskKey) {
            if (! in_array($diskKey, $candidateDisks, true)) {
                $candidateDisks[] = $diskKey;
            }
        }

        // Check disks for file existence with safe error suppression.
        foreach ($candidateDisks as $diskName) {
            try {
                $disk = Storage::disk($diskName);
            } catch (\Throwable) {
                continue;
            }

            foreach ($candidatePaths as $candidatePath) {
                // 1. Direct exists check
                try {
                    if ($disk->exists($candidatePath)) {
                        return [$disk, $candidatePath, $diskName, null];
                    }
                } catch (\Throwable $e) {
                    Log::warning("DocumentResponse: disk '{$diskName}' exists check failed for '{$candidatePath}': " . $e->getMessage());
                }

                // 2. Direct read stream probe: Essential for S3/R2 storage where HeadObject or
                // ListBucket is denied or fails silently due to 'throw' => false, but
                // GetObject/readStream is permitted and works.
                try {
                    $testStream = $disk->readStream($candidatePath);
                    if (is_resource($testStream)) {
                        fclose($testStream);
                        return [$disk, $candidatePath, $diskName, null];
                    }
                } catch (\Throwable) {
                    // Not accessible via stream on this disk
                }
            }
        }

        // Direct local filesystem checks.
        foreach ($candidatePaths as $candidatePath) {
            $cleanRelative = ltrim($candidatePath, '/\\');
            $localChecks = [
                $candidatePath,
                storage_path('app/public/' . $cleanRelative),
                storage_path('app/private/' . $cleanRelative),
                storage_path('app/' . $cleanRelative),
                public_path('storage/' . $cleanRelative),
                public_path($cleanRelative),
                storage_path('app/public/' . basename($cleanRelative)),
                storage_path('app/' . basename($cleanRelative)),
                storage_path('app/livewire-tmp/' . basename($cleanRelative)),
                storage_path('app/livewire-tmp/' . $cleanRelative),
            ];

            foreach ($localChecks as $localPath) {
                if ($localPath && is_string($localPath) && file_exists($localPath) && is_file($localPath)) {
                    $resolvedAbs = realpath($localPath) ?: $localPath;
                    try {
                        return [Storage::disk('local'), $cleanRelative, 'local', $resolvedAbs];
                    } catch (\Throwable) {
                        return [null, $cleanRelative, 'local', $resolvedAbs];
                    }
                }
            }
        }

        // Glob fallback across storage/app subdirectories for the exact basename
        $base = basename($normalized);
        if ($base !== '') {
            $potentialMatches = glob(storage_path('app/*/' . $base)) ?: [];
            $potentialMatches = array_merge($potentialMatches, glob(storage_path('app/*/*/' . $base)) ?: []);
            foreach ($potentialMatches as $match) {
                if (is_string($match) && is_file($match)) {
                    $resolvedAbs = realpath($match) ?: $match;
                    try {
                        return [Storage::disk('local'), $normalized, 'local', $resolvedAbs];
                    } catch (\Throwable) {
                        return [null, $normalized, 'local', $resolvedAbs];
                    }
                }
            }
        }

        return [null, null, null, null];
    }

    /**
     * Safely serve or download a file from storage.
     */
    public static function serve(
        ?string $rawPath,
        ?string $downloadName = null,
        bool $isDownload = false,
        array $extraHeaders = [],
        ?string $preferredDisk = null
    ): Response {
        [$disk, $path, $diskName, $localAbsolutePath] = static::resolveFile($rawPath, $preferredDisk);

        if (! $path || (! $disk && ! $localAbsolutePath)) {
            return static::notFoundResponse($isDownload, $extraHeaders, $rawPath);
        }

        $filename = $downloadName ?: basename($path);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION) ?: pathinfo($path, PATHINFO_EXTENSION));
        $unsafeInlineExtensions = ['html', 'htm', 'svg', 'js', 'mjs', 'php', 'phar', 'sh', 'bat', 'cmd', 'exe', 'xml'];
        if (in_array($ext, $unsafeInlineExtensions, true)) {
            $isDownload = true;
        }

        $mimeType = static::detectMimeType($filename) ?: static::detectMimeType($path);
        if (in_array($ext, ['html', 'htm', 'svg', 'js', 'mjs', 'php', 'phar'], true)) {
            $mimeType = 'application/octet-stream';
        }
        $dispositionType = $isDownload ? 'attachment' : 'inline';

        $headers = array_merge([
            'Content-Type' => $mimeType,
            'Content-Disposition' => $dispositionType . '; filename="' . addcslashes($filename, '"') . '"',
            'X-Content-Type-Options' => 'nosniff',
        ], $extraHeaders);

        // Serve local physical file directly if available.
        if ($localAbsolutePath && file_exists($localAbsolutePath)) {
            try {
                return response()->file($localAbsolutePath, $headers);
            } catch (\Throwable $e) {
                Log::warning("DocumentResponse: local file response failed for {$localAbsolutePath}: {$e->getMessage()}");
            }
        }

        if ($disk) {
            // Attempt 1: Stream file via readStream with verified stream resource.
            try {
                $stream = $disk->readStream($path);
                if (is_resource($stream)) {
                    try {
                        $size = $disk->size($path);
                        if ($size !== false && $size !== null && $size > 0) {
                            $headers['Content-Length'] = (string) $size;
                        }
                    } catch (\Throwable) {
                        // Safe fallback without Content-Length
                    }

                    return new StreamedResponse(function () use ($stream) {
                        try {
                            fpassthru($stream);
                        } finally {
                            if (is_resource($stream)) {
                                fclose($stream);
                            }
                        }
                    }, 200, $headers);
                }
            } catch (\Throwable $e) {
                Log::warning("DocumentResponse: readStream failed on disk {$diskName} for {$path}: {$e->getMessage()}");
            }

            // Attempt 2: Buffered string read via get().
            try {
                $content = $disk->get($path);
                if ($content !== null && $content !== false) {
                    $headers['Content-Length'] = (string) strlen($content);

                    return response($content, 200, $headers);
                }
            } catch (\Throwable $e) {
                Log::warning("DocumentResponse: buffer get failed on disk {$diskName} for {$path}: {$e->getMessage()}");
            }

            // Attempt 3: Temporary signed URL redirect for cloud storage.
            try {
                if (method_exists($disk, 'temporaryUrl')) {
                    $temporaryUrl = $disk->temporaryUrl($path, now()->addMinutes(30));
                    if ($temporaryUrl) {
                        return redirect()->away($temporaryUrl);
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("DocumentResponse: temporaryUrl failed on disk {$diskName} for {$path}: {$e->getMessage()}");
            }

            // Attempt 4: Public URL redirect.
            try {
                $url = $disk->url($path);
                if ($url && ! str_starts_with($url, '/file/')) {
                    return redirect()->away($url);
                }
            } catch (\Throwable $e) {
                Log::warning("DocumentResponse: url redirect failed on disk {$diskName} for {$path}: {$e->getMessage()}");
            }
        }

        return static::notFoundResponse($isDownload, $extraHeaders, $rawPath);
    }

    /**
     * Generate a graceful 404 response.
     */
    public static function notFoundResponse(bool $isDownload = false, array $extraHeaders = [], ?string $rawPath = null): Response
    {
        if ($isDownload) {
            abort(404, 'The requested document could not be found.');
        }

        $headers = array_merge([
            'Content-Type' => 'text/html',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "frame-ancestors 'self'",
        ], $extraHeaders);

        $user = auth()->user();
        $adminInfo = '';
        if ($user && ($user->isAdmin() || $user->isInstructor()) && $rawPath) {
            $escapedPath = htmlspecialchars($rawPath, ENT_QUOTES, 'UTF-8');
            $publicDriver = htmlspecialchars((string) config('filesystems.disks.public.driver', 'local'), ENT_QUOTES, 'UTF-8');
            $bucket = htmlspecialchars((string) config('filesystems.disks.public.bucket', '(none)'), ENT_QUOTES, 'UTF-8');
            $region = htmlspecialchars((string) config('filesystems.disks.public.region', '(none)'), ENT_QUOTES, 'UTF-8');
            $endpoint = htmlspecialchars((string) config('filesystems.disks.public.endpoint', '(default AWS)'), ENT_QUOTES, 'UTF-8');

            $keyRaw = (string) config('filesystems.disks.public.key', '');
            $keyStatus = empty($keyRaw) ? 'Missing' : htmlspecialchars(substr($keyRaw, 0, 4) . '...' . substr($keyRaw, -4), ENT_QUOTES, 'UTF-8');
            $secretStatus = empty(config('filesystems.disks.public.secret')) ? 'Missing' : 'Configured';

            $liveTelemetry = '';
            if (config('filesystems.disks.public.driver') === 's3') {
                try {
                    $testConfig = (array) config('filesystems.disks.public', []);
                    $testConfig['throw'] = true;
                    $testConfig['report'] = false;
                    $testDisk = Storage::build($testConfig);

                    // 1. Probe S3 write permission
                    $probeKey = '_diag_probe_' . time() . '.tmp';
                    try {
                        $testDisk->put($probeKey, 'ok');
                        $testDisk->delete($probeKey);
                        $liveTelemetry .= '<div style="margin-top:0.35rem;color:#16a34a;"><strong>Write Check:</strong> Bucket is writable (S3 connected)</div>';
                    } catch (\Throwable $we) {
                        $liveTelemetry .= '<div style="margin-top:0.35rem;color:#dc2626;"><strong>Write Check FAILED:</strong> ' . htmlspecialchars($we->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
                    }

                    // 2. Probe file read
                    try {
                        $stream = $testDisk->readStream($rawPath);
                        if (is_resource($stream)) {
                            fclose($stream);
                            $liveTelemetry .= '<div style="margin-top:0.35rem;color:#16a34a;"><strong>Read Check:</strong> File exists and is readable in bucket!</div>';
                        } else {
                            $liveTelemetry .= '<div style="margin-top:0.35rem;color:#dc2626;"><strong>Read Check:</strong> File not found in bucket (NoSuchKey).</div>';
                        }
                    } catch (\Throwable $re) {
                        $liveTelemetry .= '<div style="margin-top:0.35rem;color:#dc2626;"><strong>Read Check FAILED:</strong> ' . htmlspecialchars($re->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
                    }
                } catch (\Throwable $ie) {
                    $liveTelemetry .= '<div style="margin-top:0.35rem;color:#dc2626;"><strong>S3 Init Error:</strong> ' . htmlspecialchars($ie->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
                }
            }

            $adminInfo = '<div style="margin-top:1.25rem;padding:0.85rem 1.1rem;background:#fef2f2;border:1px solid #fecaca;border-radius:8px;font-size:0.78rem;color:#991b1b;text-align:left;max-width:540px;word-break:break-all;line-height:1.5;">'
                . '<strong style="font-size:0.85rem;color:#7f1d1d;">Admin Storage Diagnostic:</strong><br>'
                . 'Target Path in DB: <code>' . $escapedPath . '</code><br>'
                . 'Public Driver: <code>' . $publicDriver . '</code> | Bucket: <code>' . $bucket . '</code><br>'
                . 'Region: <code>' . $region . '</code> | Endpoint: <code>' . $endpoint . '</code><br>'
                . 'Key: <code>' . $keyStatus . '</code> | Secret: <code>' . $secretStatus . '</code>'
                . $liveTelemetry
                . '<div style="margin-top:0.6rem;padding-top:0.5rem;border-top:1px dashed #fca5a5;color:#b91c1c;">'
                . '<strong>Action Needed:</strong> If Write Check fails, your S3/AWS credentials in <code>.env</code> are incorrect or lack bucket write permissions. '
                . 'If Write Check succeeds but Read Check fails, the file was not uploaded to this bucket (e.g. uploaded before S3 was enabled). '
                . 'Please edit this item in the Admin/Instructor panel and re-upload the file.'
                . '</div>'
                . '</div>';
        }

        return response(
            '<html><head><title>File Not Found</title></head><body style="margin:0;display:flex;align-items:center;justify-content:center;height:100vh;font-family:system-ui,sans-serif;color:#6b7280;background:#f9fafb;">'
            .'<div style="text-align:center;padding:2rem;display:flex;flex-direction:column;align-items:center;"><svg xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px;margin:0 auto 1rem;color:#d1d5db;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>'
            .'<p style="margin:0 0 0.5rem;font-size:1rem;font-weight:600;color:#1f2937;">File not found</p>'
            .'<p style="margin:0;font-size:0.85rem;">The file may have been removed or is not yet available in storage.</p>'
            . $adminInfo
            .'</div></body></html>',
            404,
            $headers
        );
    }
}
