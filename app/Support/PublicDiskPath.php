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

        foreach ([
            'storage/app/public/',
            'app/public/',
            'public/storage/',
            'storage/',
            'public/',
        ] as $prefix) {
            if (str_starts_with($normalized, $prefix)) {
                $normalized = substr($normalized, strlen($prefix));
                break;
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

        return Storage::disk('public')->url($normalized);
    }
}