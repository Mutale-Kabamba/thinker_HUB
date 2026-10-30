<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class PublicDiskPath
{
    public static function normalize(?string $path): ?string
    {
        $original = trim((string) $path);

        if ($original === '') {
            return null;
        }

        $normalized = $original;

        if (filter_var($original, FILTER_VALIDATE_URL)) {
            $urlPath = (string) parse_url($original, PHP_URL_PATH);
            $query = (string) parse_url($original, PHP_URL_QUERY);

            if ($urlPath !== '' && str_ends_with($urlPath, '/file/public')) {
                parse_str($query, $queryParams);
                $normalized = (string) ($queryParams['path'] ?? '');
            } else {
                $normalized = $urlPath !== '' ? $urlPath : $original;
            }
        }

        $normalized = rawurldecode(str_replace('\\', '/', $normalized));
        $normalized = preg_replace('#/+#', '/', $normalized) ?? $normalized;
        $normalized = ltrim($normalized, '/');

        // If the path contains an embedded storage prefix (e.g. from an absolute path or full URL)
        foreach ([
            'storage/app/public/',
            'storage/app/private/',
            'storage/app/',
            'public/storage/',
            'app/public/',
            'app/private/',
            'app/',
            'storage/',
            'public/',
        ] as $prefix) {
            $pos = strpos($normalized, $prefix);
            if ($pos !== false) {
                // If it starts with it or is preceded by a slash or drive colon (e.g. C:/.../storage/app/public/)
                if ($pos === 0 || in_array($normalized[$pos - 1], ['/', ':'], true)) {
                    $normalized = substr($normalized, $pos + strlen($prefix));
                    break;
                }
            }
        }

        $normalized = ltrim($normalized, '/');

        return $normalized === '' ? null : $normalized;
    }

    public static function url(?string $path): ?string
    {
        $normalized = static::normalize($path);

        if (! $normalized) {
            return null;
        }

        if (config('filesystems.disks.public.driver') === 'local') {
            return '/storage/'.ltrim($normalized, '/');
        }

        try {
            return Storage::disk('public')->url($normalized);
        } catch (\Throwable) {
            return '/storage/'.ltrim($normalized, '/');
        }
    }
}