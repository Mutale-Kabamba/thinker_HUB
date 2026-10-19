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
        'zip' => 'application/zip',
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
        $normalized = PublicDiskPath::normalize($rawPath);

        if (! $normalized) {
            return [null, null, null, null];
        }

        // Generate candidate path variations.
        $candidatePaths = [$normalized];
        $baseName = basename($normalized);

        foreach (['submissions/', 'learning-materials/', 'attachments/', 'materials/', 'courses/'] as $prefix) {
            $variation = $prefix . $baseName;
            if (! in_array($variation, $candidatePaths, true)) {
                $candidatePaths[] = $variation;
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

        // Check disks for file existence with safe error suppression.
        foreach ($candidateDisks as $diskName) {
            try {
                $disk = Storage::disk($diskName);
            } catch (\Throwable) {
                continue;
            }

            foreach ($candidatePaths as $candidatePath) {
                try {
                    if ($disk->exists($candidatePath)) {
                        return [$disk, $candidatePath, $diskName, null];
                    }
                } catch (\Throwable) {
                    continue;
                }
            }
        }

        // Direct local filesystem checks.
        foreach ($candidatePaths as $candidatePath) {
            $localChecks = [
                storage_path('app/public/' . $candidatePath),
                storage_path('app/private/' . $candidatePath),
                storage_path('app/' . $candidatePath),
                public_path('storage/' . $candidatePath),
            ];

            foreach ($localChecks as $localPath) {
                if (file_exists($localPath) && is_file($localPath)) {
                    try {
                        return [Storage::disk('local'), $candidatePath, 'local', $localPath];
                    } catch (\Throwable) {
                        return [null, $candidatePath, 'local', $localPath];
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
            return static::notFoundResponse($isDownload);
        }

        $filename = $downloadName ?: basename($path);
        $mimeType = static::detectMimeType($filename) ?: static::detectMimeType($path);
        $dispositionType = $isDownload ? 'attachment' : 'inline';

        $headers = array_merge([
            'Content-Type' => $mimeType,
            'Content-Disposition' => $dispositionType . '; filename="' . addcslashes($filename, '"') . '"',
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

        return static::notFoundResponse($isDownload);
    }

    /**
     * Generate a graceful 404 response.
     */
    public static function notFoundResponse(bool $isDownload = false): Response
    {
        if ($isDownload) {
            abort(404, 'The requested document could not be found.');
        }

        return response(
            '<html><body style="margin:0;display:flex;align-items:center;justify-content:center;height:100vh;font-family:system-ui,sans-serif;color:#6b7280;background:#f9fafb;">'
            .'<div style="text-align:center;padding:2rem;"><svg xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px;margin:0 auto 1rem;color:#d1d5db;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>'
            .'<p style="margin:0 0 0.5rem;font-size:1rem;font-weight:600;">File not found</p>'
            .'<p style="margin:0;font-size:0.85rem;">The file may have been removed or is not yet available.</p></div></body></html>',
            404,
            ['Content-Type' => 'text/html']
        );
    }
}
