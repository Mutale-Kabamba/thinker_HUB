<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\ResourceDownload;
use App\Models\ScormAttempt;
use App\Services\ProgressionService;
use App\Services\ScormPackageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OfflineLearningController extends Controller
{
    /**
     * Render the standalone Offline Library & Player shell (cached by Service Worker for 100% offline use).
     */
    public function library(): View
    {
        return view('offline.library');
    }

    /**
     * Export an enrolled course into a self-contained JSON bundle + cacheable asset manifest
     * for browser IndexedDB & Cache Storage.
     */
    public function courseBundle(Request $request, Course $course): JsonResponse
    {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $isEnrolled = $user->isAdmin()
            || $user->isInstructor()
            || Enrollment::query()->where('user_id', $user->id)->where('course_id', $course->id)->exists();

        if (! $isEnrolled) {
            abort(403, 'You must be enrolled in this course to download it for offline study.');
        }

        $course->load(['sections', 'lessons.scormPackage']);
        $progressMap = LessonProgress::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->get()
            ->keyBy('lesson_id');

        $assetUrls = [];
        $lessonsPayload = [];

        foreach ($course->lessons->sortBy('order_column')->values() as $lesson) {
            $progress = $progressMap->get($lesson->id);

            // Prefer low-bandwidth direct video when available, otherwise direct video URL (skip 3rd-party embeds like YouTube)
            $directVideoUrl = null;
            if (! empty($lesson->low_bandwidth_video_url) && $this->isCacheableDirectUrl($lesson->low_bandwidth_video_url)) {
                $directVideoUrl = $lesson->low_bandwidth_video_url;
            } elseif (! empty($lesson->video_url) && $this->isCacheableDirectUrl($lesson->video_url)) {
                $directVideoUrl = $lesson->video_url;
            }

            if ($directVideoUrl) {
                $assetUrls[] = $directVideoUrl;
            }

            $resources = [];
            $rawFiles = is_array($lesson->resource_files) && ! empty($lesson->resource_files)
                ? array_values($lesson->resource_files)
                : (is_array($lesson->attachments) ? array_values($lesson->attachments) : []);

            foreach ($rawFiles as $idx => $entry) {
                $path = is_array($entry) ? (string) ($entry['path'] ?? $entry['file_path'] ?? '') : (string) $entry;
                $name = is_array($entry) ? (string) ($entry['name'] ?? basename($path)) : basename($path);
                if ($path === '') {
                    continue;
                }

                $url = str_starts_with($path, 'http://') || str_starts_with($path, 'https://')
                    ? $path
                    : Storage::disk('public')->url($path);

                $resources[] = [
                    'index' => $idx,
                    'name' => $name ?: 'Resource',
                    'url' => $url,
                ];

                if (str_starts_with($url, '/') || str_starts_with($url, $request->getSchemeAndHttpHost())) {
                    $assetUrls[] = $url;
                }
            }

            $scormLaunchUrl = null;
            if ($lesson->type === 'scorm' && $lesson->scormPackage) {
                $scormLaunchUrl = $lesson->scormPackage->launch_url;
                if (str_starts_with($scormLaunchUrl, '/') || str_starts_with($scormLaunchUrl, $request->getSchemeAndHttpHost())) {
                    $assetUrls[] = $scormLaunchUrl;
                }
            }

            $lessonsPayload[] = [
                'id' => $lesson->id,
                'title' => $lesson->title,
                'type' => $lesson->type ?: 'video',
                'order_column' => (int) ($lesson->order_column ?? 0),
                'reading_body' => $lesson->reading_body,
                'video_url' => $directVideoUrl,
                'external_video_url' => $directVideoUrl ? null : $lesson->video_url,
                'min_watch_percentage' => (int) ($lesson->min_watch_percentage ?: 85),
                'resources' => $resources,
                'scorm_launch_url' => $scormLaunchUrl,
                'is_completed' => (bool) ($progress?->is_completed ?? false),
                'last_watched_second' => (int) ($progress?->last_watched_second ?? 0),
            ];
        }

        return response()->json([
            'course' => [
                'id' => $course->id,
                'title' => $course->title,
                'slug' => $course->slug,
                'code' => $course->code,
                'downloaded_at' => now()->toIso8601String(),
                'lessons' => $lessonsPayload,
            ],
            'asset_urls' => array_values(array_unique($assetUrls)),
        ]);
    }

    /**
     * Idempotently replay queued offline learning actions once connectivity returns.
     */
    public function sync(
        Request $request,
        ProgressionService $progressionService,
        ScormPackageService $scormService,
    ): JsonResponse {
        $user = $request->user();
        if (! $user) {
            abort(401);
        }

        $validated = $request->validate([
            'events' => ['required', 'array', 'max:100'],
            'events.*.id' => ['nullable', 'string', 'max:100'],
            'events.*.type' => ['required', 'string', 'in:lesson_complete,video_progress,scorm_commit,resource_download'],
            'events.*.payload' => ['required', 'array'],
        ]);

        $syncedIds = [];
        $errors = [];

        foreach ($validated['events'] as $index => $event) {
            $eventId = (string) ($event['id'] ?? $index);
            $type = $event['type'];
            $payload = $event['payload'];

            try {
                if ($type === 'lesson_complete') {
                    $lesson = Lesson::find((int) ($payload['lesson_id'] ?? 0));
                    if ($lesson) {
                        $progressionService->completeAndAdvance($user, $lesson);
                    }
                    $syncedIds[] = $eventId;
                } elseif ($type === 'video_progress') {
                    $lesson = Lesson::find((int) ($payload['lesson_id'] ?? 0));
                    $seconds = max(0, (int) ($payload['seconds'] ?? 0));
                    if ($lesson) {
                        LessonProgress::updateOrCreate(
                            ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                            [
                                'course_id' => $lesson->course_id,
                                'is_unlocked' => true,
                                'last_watched_second' => $seconds,
                            ]
                        );
                    }
                    $syncedIds[] = $eventId;
                } elseif ($type === 'scorm_commit') {
                    $attempt = ScormAttempt::where('id', (int) ($payload['attempt_id'] ?? 0))
                        ->where('user_id', $user->id)
                        ->first();
                    if ($attempt && is_array($payload['cmi'] ?? null)) {
                        $scormService->commitCmiData($attempt, $payload['cmi']);
                    }
                    $syncedIds[] = $eventId;
                } elseif ($type === 'resource_download') {
                    $lesson = Lesson::find((int) ($payload['lesson_id'] ?? 0));
                    if ($lesson) {
                        ResourceDownload::create([
                            'user_id' => $user->id,
                            'tenant_id' => $user->tenant_id ?? $lesson->course?->tenant_id,
                            'course_id' => $lesson->course_id,
                            'lesson_id' => $lesson->id,
                            'file_name' => mb_substr((string) ($payload['file_name'] ?? 'Resource'), 0, 255),
                            'file_path' => isset($payload['file_path']) ? mb_substr((string) $payload['file_path'], 0, 500) : null,
                            'downloaded_at' => now(),
                        ]);
                    }
                    $syncedIds[] = $eventId;
                }
            } catch (\Throwable $e) {
                $errors[$eventId] = $e->getMessage();
            }
        }

        return response()->json([
            'status' => 'ok',
            'synced_ids' => $syncedIds,
            'synced_count' => count($syncedIds),
            'errors' => $errors,
        ]);
    }

    private function isCacheableDirectUrl(string $url): bool
    {
        $lower = strtolower($url);
        if (str_contains($lower, 'youtube.com') || str_contains($lower, 'youtu.be') || str_contains($lower, 'vimeo.com') || str_contains($lower, 'loom.com')) {
            return false;
        }

        return (bool) preg_match('/\.(mp4|webm|ogg|m4v)(\?.*)?$/i', $lower);
    }
}
