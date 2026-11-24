<?php

namespace App\Http\Controllers;

use App\Models\Lesson;
use App\Models\ResourceDownload;
use App\Models\ScormAttempt;
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
