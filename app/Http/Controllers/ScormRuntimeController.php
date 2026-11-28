<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\ResourceDownload;
use App\Models\ScormAttempt;
use App\Models\ScormPackage;
use App\Services\ScormPackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class ScormRuntimeController extends Controller
{
    public function __construct(private readonly ScormPackageService $scormService)
    {
    }

    /**
     * Serve extracted SCORM / xAPI package files same-origin so window.parent.API
     * and window.parent.API_1484_11 work seamlessly even when files live on S3 / Cloudflare R2.
     */
    public function content(Request $request, ScormPackage $package, string $path = ''): Response
    {
        $cleanPath = ltrim(str_replace('\\', '/', $path ?: $package->entry_point), '/');
        if ($cleanPath === '' || str_contains($cleanPath, '..')) {
            abort(403, 'Invalid SCORM asset path.');
        }

        $disk = Storage::disk($package->storage_disk ?: 'public');
        $fullPath = trim($package->storage_path, '/') . '/' . $cleanPath;

        if (! $disk->exists($fullPath)) {
            abort(404, 'SCORM asset not found.');
        }

        $ext = strtolower(pathinfo($cleanPath, PATHINFO_EXTENSION));
        $mime = match ($ext) {
            'html', 'htm' => 'text/html; charset=UTF-8',
            'js', 'mjs' => 'application/javascript; charset=UTF-8',
            'css' => 'text/css; charset=UTF-8',
            'json' => 'application/json; charset=UTF-8',
            'xml' => 'application/xml; charset=UTF-8',
            'svg' => 'image/svg+xml',
            'png' => 'image/png',
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'mp4' => 'video/mp4',
            'webm' => 'video/webm',
            'mp3' => 'audio/mpeg',
            'woff2' => 'font/woff2',
            'woff' => 'font/woff',
            'pdf' => 'application/pdf',
            default => $disk->mimeType($fullPath) ?: 'application/octet-stream',
        };

        $contents = $disk->get($fullPath);

        return response($contents, 200, [
            'Content-Type' => $mime,
            'Cache-Control' => 'private, max-age=3600',
            'X-Frame-Options' => 'SAMEORIGIN',
        ]);
    }

    /**
     * Persist SCORM 1.2 / 2004 CMI elements from the browser JS Runtime API adapter.
     */
    public function commit(Request $request, ScormAttempt $attempt): JsonResponse
    {
        $user = $request->user();
        if (! $user || ((int) $attempt->user_id !== (int) $user->id && ! $user->isAdmin())) {
            abort(403);
        }

        $validated = $request->validate([
            'cmi' => ['required', 'array'],
        ]);

        $updated = $this->scormService->commitCmiData($attempt, $validated['cmi']);

        return response()->json([
            'status' => 'ok',
            'attempt_id' => $updated->id,
            'lesson_status' => $updated->lesson_status,
            'score_raw' => $updated->score_raw,
            'lesson_location' => $updated->lesson_location,
            'completed' => $updated->isCompletedOrPassed(),
        ]);
    }

    /**
     * Ingest an xAPI (Tin Can) statement emitted by an xAPI module.
     */
    public function xapi(Request $request, ScormAttempt $attempt): JsonResponse
    {
        $user = $request->user();
        if (! $user || ((int) $attempt->user_id !== (int) $user->id && ! $user->isAdmin())) {
            abort(403);
        }

        $payload = $request->all();
        $updated = $this->scormService->recordXapiStatement($attempt, $payload);

        return response()->json([
            'status' => 'ok',
            'attempt_id' => $updated->id,
            'lesson_status' => $updated->lesson_status,
            'score_raw' => $updated->score_raw,
            'completed' => $updated->isCompletedOrPassed(),
        ]);
    }

    /**
     * Track and serve a downloadable lesson resource file (PDF, worksheet, offline packet).
     */
    public function downloadLessonResource(Request $request, Lesson $lesson, int $index): Response
    {
        $user = $request->user();
        if (! $user) {
            abort(403);
        }

        $files = is_array($lesson->resource_files) ? array_values($lesson->resource_files) : [];
        if (empty($files) && is_array($lesson->attachments)) {
            $files = array_values($lesson->attachments);
        }

        $entry = $files[$index] ?? null;
        if (! $entry) {
            abort(404, 'Resource file not found.');
        }

        $path = is_array($entry) ? (string) ($entry['path'] ?? $entry['file_path'] ?? '') : (string) $entry;
        $name = is_array($entry) ? (string) ($entry['name'] ?? basename($path)) : basename($path);

        ResourceDownload::create([
            'user_id' => $user->id,
            'tenant_id' => $user->tenant_id ?? $lesson->course?->tenant_id,
            'course_id' => $lesson->course_id,
            'lesson_id' => $lesson->id,
            'file_name' => $name ?: 'resource',
            'file_path' => $path,
            'downloaded_at' => now(),
        ]);

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return redirect()->away($path);
        }

        $disk = Storage::disk('public');
        if (! $disk->exists($path)) {
            abort(404, 'File does not exist in storage.');
        }

        return $disk->download($path, $name ?: basename($path));
    }
}
