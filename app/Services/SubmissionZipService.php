<?php

namespace App\Services;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Support\PublicDiskPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class SubmissionZipService
{
    /**
     * Download selected assignment submissions as a ZIP archive.
     */
    public function downloadAssignmentsZip(Collection|array $submissions, string $zipNamePrefix = 'Assignment_Submissions'): ?StreamedResponse
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
            $submissionFilesAdded = 0;

            foreach ($paths as $idx => $rawPath) {
                $resolvedPath = $this->resolveFilePath($rawPath, $disk);

                if ($resolvedPath && file_exists($resolvedPath) && is_file($resolvedPath)) {
                    $ext = pathinfo($resolvedPath, PATHINFO_EXTENSION);
                    $ext = $ext ? '.' . $ext : '';

                    $suffix = count($paths) > 1 ? '_' . ($idx + 1) : '';
                    $baseName = "{$studentFirst}_{$assignmentSlug}{$suffix}";

                    $entryName = $this->uniqueEntryName($baseName, $ext, $usedNames);
                    $zip->addFile($resolvedPath, $entryName);
                    $filesAdded++;
                    $submissionFilesAdded++;
                }
            }

            // If no physical attachment file existed, check if student submitted text content or link
            if ($submissionFilesAdded === 0 && (filled($submission->content) || filled($submission->link) || filled($submission->video_url))) {
                $textData = "Student: " . ($user?->name ?? 'Student') . "\n";
                $textData .= "Assignment: " . ($assignment?->name ?? 'Assignment') . "\n";
                $textData .= "Submitted At: " . ($submission->submitted_at?->toDateTimeString() ?? now()->toDateTimeString()) . "\n";
                $textData .= "Status: " . ($submission->status ?? 'Submitted') . "\n\n";

                if (filled($submission->content)) {
                    $textData .= "--- SUBMISSION CONTENT ---\n" . $submission->content . "\n\n";
                }
                if (filled($submission->link)) {
                    $textData .= "Submission Link: " . $submission->link . "\n";
                }
                if (filled($submission->video_url)) {
                    $textData .= "Video URL: " . $submission->video_url . "\n";
                }

                $baseName = "{$studentFirst}_{$assignmentSlug}_text_submission";
                $entryName = $this->uniqueEntryName($baseName, '.txt', $usedNames);
                $zip->addFromString($entryName, $textData);
                $filesAdded++;
            }
        }

        $zip->close();

        if ($filesAdded === 0) {
            if (file_exists($tempZipPath)) {
                @unlink($tempZipPath);
            }

            return null;
        }

        return response()->streamDownload(function () use ($tempZipPath) {
            if (file_exists($tempZipPath)) {
                $stream = fopen($tempZipPath, 'rb');
                fpassthru($stream);
                fclose($stream);
                @unlink($tempZipPath);
            }
        }, $zipFileName, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
        ]);
    }

    /**
     * Download selected assessment submissions as a ZIP archive.
     */
    public function downloadAssessmentsZip(Collection|array $submissions, string $zipNamePrefix = 'Assessment_Submissions'): ?StreamedResponse
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
            $submissionFilesAdded = 0;

            foreach ($paths as $idx => $rawPath) {
                $resolvedPath = $this->resolveFilePath($rawPath, $disk);

                if ($resolvedPath && file_exists($resolvedPath) && is_file($resolvedPath)) {
                    $ext = pathinfo($resolvedPath, PATHINFO_EXTENSION);
                    $ext = $ext ? '.' . $ext : '';

                    $suffix = count($paths) > 1 ? '_' . ($idx + 1) : '';
                    $baseName = "{$studentFirst}_{$assessmentSlug}{$suffix}";

                    $entryName = $this->uniqueEntryName($baseName, $ext, $usedNames);
                    $zip->addFile($resolvedPath, $entryName);
                    $filesAdded++;
                    $submissionFilesAdded++;
                }
            }

            // If no physical attachment file existed, check if student submitted text content or link
            if ($submissionFilesAdded === 0 && (filled($submission->content) || filled($submission->link) || filled($submission->video_url))) {
                $textData = "Student: " . ($user?->name ?? 'Student') . "\n";
                $textData .= "Assessment: " . ($assessment?->name ?? 'Assessment') . "\n";
                $textData .= "Submitted At: " . ($submission->submitted_at?->toDateTimeString() ?? now()->toDateTimeString()) . "\n";
                $textData .= "Status: " . ($submission->status ?? 'Submitted') . "\n\n";

                if (filled($submission->content)) {
                    $textData .= "--- SUBMISSION CONTENT ---\n" . $submission->content . "\n\n";
                }
                if (filled($submission->link)) {
                    $textData .= "Submission Link: " . $submission->link . "\n";
                }
                if (filled($submission->video_url)) {
                    $textData .= "Video URL: " . $submission->video_url . "\n";
                }

                $baseName = "{$studentFirst}_{$assessmentSlug}_text_submission";
                $entryName = $this->uniqueEntryName($baseName, '.txt', $usedNames);
                $zip->addFromString($entryName, $textData);
                $filesAdded++;
            }
        }

        $zip->close();

        if ($filesAdded === 0) {
            if (file_exists($tempZipPath)) {
                @unlink($tempZipPath);
            }

            return null;
        }

        return response()->streamDownload(function () use ($tempZipPath) {
            if (file_exists($tempZipPath)) {
                $stream = fopen($tempZipPath, 'rb');
                fpassthru($stream);
                fclose($stream);
                @unlink($tempZipPath);
            }
        }, $zipFileName, [
            'Content-Type' => 'application/zip',
            'Content-Disposition' => 'attachment; filename="' . $zipFileName . '"',
        ]);
    }

    /**
     * Resolve file path across multiple possible storage locations.
     */
    protected function resolveFilePath(?string $rawPath, $disk): ?string
    {
        if (blank($rawPath)) {
            return null;
        }

        $normalized = PublicDiskPath::normalize($rawPath);
        if (! $normalized) {
            $normalized = ltrim($rawPath, '/\\');
        }

        $candidates = [
            $disk->path($normalized),
            storage_path('app/public/' . $normalized),
            storage_path('app/' . $normalized),
            public_path('storage/' . $normalized),
            public_path($normalized),
            $disk->path('submissions/' . basename($normalized)),
            storage_path('app/public/submissions/' . basename($normalized)),
            storage_path('app/submissions/' . basename($normalized)),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && file_exists($candidate) && is_file($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Ensure a unique name in the ZIP archive.
     */
    protected function uniqueEntryName(string $baseName, string $ext, array &$usedNames): string
    {
        $entryName = $baseName . $ext;
        $counter = 2;
        while (isset($usedNames[strtolower($entryName)])) {
            $entryName = "{$baseName}_({$counter}){$ext}";
            $counter++;
        }
        $usedNames[strtolower($entryName)] = true;

        return $entryName;
    }
}
