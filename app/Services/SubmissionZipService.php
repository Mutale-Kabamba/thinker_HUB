<?php

namespace App\Services;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Support\PublicDiskPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use ZipArchive;

class SubmissionZipService
{
    /**
     * Download selected assignment submissions as a ZIP archive.
     */
    public function downloadAssignmentsZip(Collection|array $submissions, string $zipNamePrefix = 'Assignment_Submissions'): ?BinaryFileResponse
    {
        $records = is_array($submissions) ? collect($submissions) : $submissions;

        if ($records->isEmpty()) {
            return null;
        }

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        $zipFileName = $zipNamePrefix . '_' . now()->format('Ymd_His') . '.zip';
        $tempZipPath = $tempDir . '/' . uniqid('submissions_', true) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        $disk = Storage::disk('public');
        $filesAdded = 0;
        $usedNames = [];

        foreach ($records as $submission) {
            if (! $submission instanceof AssignmentSubmission) {
                continue;
            }

            $user = $submission->user;
            $assignment = $submission->assignment;

            $rawFirst = explode(' ', trim((string) ($user?->name ?? 'Student')))[0] ?? 'Student';
            $studentFirst = Str::slug($rawFirst, '_');
            $assignmentSlug = Str::slug((string) ($assignment?->name ?? 'Assignment'), '_');

            $paths = $submission->all_file_paths;

            foreach ($paths as $idx => $rawPath) {
                $normalized = PublicDiskPath::normalize($rawPath);
                if (! $normalized) {
                    continue;
                }

                $fullPath = $disk->path($normalized);
                if (! file_exists($fullPath)) {
                    if ($disk->exists('submissions/' . basename($normalized))) {
                        $fullPath = $disk->path('submissions/' . basename($normalized));
                    }
                }

                if (file_exists($fullPath) && is_file($fullPath)) {
                    $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $ext = $ext ? '.' . $ext : '';

                    $suffix = count($paths) > 1 ? '_' . ($idx + 1) : '';
                    $baseName = "{$studentFirst}_{$assignmentSlug}{$suffix}";

                    $entryName = $baseName . $ext;
                    $counter = 2;
                    while (isset($usedNames[strtolower($entryName)])) {
                        $entryName = "{$baseName}_({$counter}){$ext}";
                        $counter++;
                    }
                    $usedNames[strtolower($entryName)] = true;

                    $zip->addFile($fullPath, $entryName);
                    $filesAdded++;
                }
            }
        }

        $zip->close();

        if ($filesAdded === 0) {
            if (file_exists($tempZipPath)) {
                @unlink($tempZipPath);
            }

            return null;
        }

        return response()->download($tempZipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Download selected assessment submissions as a ZIP archive.
     */
    public function downloadAssessmentsZip(Collection|array $submissions, string $zipNamePrefix = 'Assessment_Submissions'): ?BinaryFileResponse
    {
        $records = is_array($submissions) ? collect($submissions) : $submissions;

        if ($records->isEmpty()) {
            return null;
        }

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        $zipFileName = $zipNamePrefix . '_' . now()->format('Ymd_His') . '.zip';
        $tempZipPath = $tempDir . '/' . uniqid('assessments_', true) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return null;
        }

        $disk = Storage::disk('public');
        $filesAdded = 0;
        $usedNames = [];

        foreach ($records as $submission) {
            if (! $submission instanceof AssessmentSubmission) {
                continue;
            }

            $user = $submission->user;
            $assessment = $submission->assessment;

            $rawFirst = explode(' ', trim((string) ($user?->name ?? 'Student')))[0] ?? 'Student';
            $studentFirst = Str::slug($rawFirst, '_');
            $assessmentSlug = Str::slug((string) ($assessment?->name ?? 'Assessment'), '_');

            $paths = $submission->all_file_paths;

            foreach ($paths as $idx => $rawPath) {
                $normalized = PublicDiskPath::normalize($rawPath);
                if (! $normalized) {
                    continue;
                }

                $fullPath = $disk->path($normalized);
                if (! file_exists($fullPath)) {
                    if ($disk->exists('submissions/' . basename($normalized))) {
                        $fullPath = $disk->path('submissions/' . basename($normalized));
                    }
                }

                if (file_exists($fullPath) && is_file($fullPath)) {
                    $ext = pathinfo($fullPath, PATHINFO_EXTENSION);
                    $ext = $ext ? '.' . $ext : '';

                    $suffix = count($paths) > 1 ? '_' . ($idx + 1) : '';
                    $baseName = "{$studentFirst}_{$assessmentSlug}{$suffix}";

                    $entryName = $baseName . $ext;
                    $counter = 2;
                    while (isset($usedNames[strtolower($entryName)])) {
                        $entryName = "{$baseName}_({$counter}){$ext}";
                        $counter++;
                    }
                    $usedNames[strtolower($entryName)] = true;

                    $zip->addFile($fullPath, $entryName);
                    $filesAdded++;
                }
            }
        }

        $zip->close();

        if ($filesAdded === 0) {
            if (file_exists($tempZipPath)) {
                @unlink($tempZipPath);
            }

            return null;
        }

        return response()->download($tempZipPath, $zipFileName, [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }
}
