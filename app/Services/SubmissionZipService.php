<?php

namespace App\Services;

use App\Models\AssessmentSubmission;
use App\Models\AssignmentSubmission;
use App\Support\PublicDiskPath;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
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

            if (! empty($paths)) {
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
            }

            // If no physical attachment file was found/added on disk, create a comprehensive submission details text file
            if ($submissionFilesAdded === 0) {
                $textData = "STUDENT ASSIGNMENT SUBMISSION\n";
                $textData .= "=============================\n";
                $textData .= "Student: " . ($user?->name ?? 'Unknown Student') . "\n";
                $textData .= "Email: " . ($user?->email ?? 'N/A') . "\n";
                $textData .= "Assignment: " . ($assignment?->name ?? 'Assignment') . "\n";
                $textData .= "Course: " . ($assignment?->course?->title ?? 'N/A') . "\n";
                $textData .= "Submitted At: " . ($submission->submitted_at?->toDateTimeString() ?? 'N/A') . "\n";
                $textData .= "Status: " . ($submission->status ?? 'Submitted') . "\n";
                if ($submission->grade !== null) {
                    $textData .= "Grade: " . $submission->grade . " / 100\n";
                }
                if (filled($submission->feedback)) {
                    $textData .= "Feedback: " . $submission->feedback . "\n";
                }
                $textData .= "\n";

                if (filled($submission->content)) {
                    $textData .= "--- WRITTEN SUBMISSION CONTENT ---\n" . $submission->content . "\n\n";
                }
                if (filled($submission->link)) {
                    $textData .= "Submission Link: " . $submission->link . "\n";
                }
                if (filled($submission->video_url)) {
                    $textData .= "Video URL: " . $submission->video_url . "\n";
                }
                if (! empty($paths)) {
                    $textData .= "\n--- ATTACHMENT RECORD ---\n";
                    $textData .= "Recorded file path(s): " . implode(', ', $paths) . "\n";
                    $textData .= "Note: The physical file could not be located in server storage at the time of export.\n";
                }

                $baseName = "{$studentFirst}_{$assignmentSlug}_submission_details";
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

            if (! empty($paths)) {
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
            }

            // If no physical attachment file was found/added on disk, create a comprehensive submission details text file
            if ($submissionFilesAdded === 0) {
                $textData = "STUDENT ASSESSMENT SUBMISSION\n";
                $textData .= "=============================\n";
                $textData .= "Student: " . ($user?->name ?? 'Unknown Student') . "\n";
                $textData .= "Email: " . ($user?->email ?? 'N/A') . "\n";
                $textData .= "Assessment: " . ($assessment?->name ?? 'Assessment') . "\n";
                $textData .= "Course: " . ($assessment?->course?->title ?? 'N/A') . "\n";
                $textData .= "Submitted At: " . ($submission->submitted_at?->toDateTimeString() ?? 'N/A') . "\n";
                $textData .= "Status: " . ($submission->status ?? 'Submitted') . "\n";
                if ($submission->grade !== null) {
                    $textData .= "Grade: " . $submission->grade . " / 100\n";
                }
                if (filled($submission->feedback)) {
                    $textData .= "Feedback: " . $submission->feedback . "\n";
                }
                $textData .= "\n";

                if (filled($submission->content)) {
                    $textData .= "--- WRITTEN SUBMISSION CONTENT ---\n" . $submission->content . "\n\n";
                }
                if (filled($submission->link)) {
                    $textData .= "Submission Link: " . $submission->link . "\n";
                }
                if (filled($submission->video_url)) {
                    $textData .= "Video URL: " . $submission->video_url . "\n";
                }
                if (! empty($paths)) {
                    $textData .= "\n--- ATTACHMENT RECORD ---\n";
                    $textData .= "Recorded file path(s): " . implode(', ', $paths) . "\n";
                    $textData .= "Note: The physical file could not be located in server storage at the time of export.\n";
                }

                $baseName = "{$studentFirst}_{$assessmentSlug}_submission_details";
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
     * Download a single assignment submission file or zip.
     */
    public function downloadSingleAssignmentSubmission(AssignmentSubmission $submission): Response|StreamedResponse|null
    {
        $paths = $submission->all_file_paths;
        $disk = Storage::disk('public');

        if (count($paths) === 1) {
            $resolved = $this->resolveFilePath($paths[0], $disk);
            if ($resolved && file_exists($resolved) && is_file($resolved)) {
                $user = $submission->user;
                $assignment = $submission->assignment;
                $rawFirst = explode(' ', trim((string) ($user?->name ?? 'Student')))[0] ?? 'Student';
                $studentFirst = Str::slug($rawFirst, '_');
                $assignmentSlug = Str::slug((string) ($assignment?->name ?? 'Assignment'), '_');
                $ext = pathinfo($resolved, PATHINFO_EXTENSION);
                $downloadName = "{$studentFirst}_{$assignmentSlug}" . ($ext ? '.' . $ext : '');

                return response()->download($resolved, $downloadName);
            }
        }

        return $this->downloadAssignmentsZip(collect([$submission]));
    }

    /**
     * Download a single assessment submission file or zip.
     */
    public function downloadSingleAssessmentSubmission(AssessmentSubmission $submission): Response|StreamedResponse|null
    {
        $paths = $submission->all_file_paths;
        $disk = Storage::disk('public');

        if (count($paths) === 1) {
            $resolved = $this->resolveFilePath($paths[0], $disk);
            if ($resolved && file_exists($resolved) && is_file($resolved)) {
                $user = $submission->user;
                $assessment = $submission->assessment;
                $rawFirst = explode(' ', trim((string) ($user?->name ?? 'Student')))[0] ?? 'Student';
                $studentFirst = Str::slug($rawFirst, '_');
                $assessmentSlug = Str::slug((string) ($assessment?->name ?? 'Assessment'), '_');
                $ext = pathinfo($resolved, PATHINFO_EXTENSION);
                $downloadName = "{$studentFirst}_{$assessmentSlug}" . ($ext ? '.' . $ext : '');

                return response()->download($resolved, $downloadName);
            }
        }

        return $this->downloadAssessmentsZip(collect([$submission]));
    }

    /**
     * Resolve file path across multiple possible storage locations.
     */
    public function resolveFilePath(?string $rawPath, $disk = null): ?string
    {
        if (blank($rawPath)) {
            return null;
        }

        $disk = $disk ?: Storage::disk('public');

        // If direct valid file path
        if (file_exists($rawPath) && is_file($rawPath)) {
            return $rawPath;
        }

        // Clean any scheme/host or storage prefixes
        $cleanPath = preg_replace('#^https?://[^/]+/storage/#i', '', $rawPath);
        $cleanPath = preg_replace('#^https?://[^/]+/#i', '', $cleanPath);
        $cleanPath = str_replace('\\', '/', $cleanPath);
        $cleanPath = ltrim($cleanPath, '/');

        $normalized = PublicDiskPath::normalize($cleanPath);
        if (! $normalized) {
            $normalized = $cleanPath;
        }

        $baseName = basename($cleanPath);

        $candidates = [
            $rawPath,
            $cleanPath,
            $disk->path($normalized),
            $disk->path($cleanPath),
            storage_path('app/public/' . $normalized),
            storage_path('app/public/' . $cleanPath),
            storage_path('app/' . $normalized),
            storage_path('app/' . $cleanPath),
            public_path('storage/' . $normalized),
            public_path('storage/' . $cleanPath),
            public_path($normalized),
            public_path($cleanPath),
            $disk->path('submissions/' . $baseName),
            storage_path('app/public/submissions/' . $baseName),
            storage_path('app/submissions/' . $baseName),
            public_path('storage/submissions/' . $baseName),
            $disk->path($baseName),
            storage_path('app/public/' . $baseName),
        ];

        foreach ($candidates as $candidate) {
            if ($candidate && is_string($candidate) && file_exists($candidate) && is_file($candidate)) {
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
