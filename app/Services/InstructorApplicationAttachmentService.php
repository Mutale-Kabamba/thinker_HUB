<?php

namespace App\Services;

use App\Models\InstructorApplication;
use App\Support\PublicDiskPath;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class InstructorApplicationAttachmentService
{
    /**
     * Determine if an application has any recorded attachment paths.
     */
    public function hasAttachments(InstructorApplication $application): bool
    {
        return filled($application->roadmap_path)
            || filled($application->full_roadmap_path)
            || filled($application->curriculum_path)
            || filled($application->cv_path);
    }

    /**
     * Get list of all attachments defined on the application.
     */
    public function getAttachmentsList(InstructorApplication $application): array
    {
        $docs = [
            'roadmap' => [
                'type' => 'roadmap',
                'label' => 'Course Roadmap',
                'path' => $application->roadmap_path,
                'default_filename' => 'Course_Roadmap',
            ],
            'full_roadmap' => [
                'type' => 'full_roadmap',
                'label' => 'Full Curriculum Roadmap',
                'path' => $application->full_roadmap_path,
                'default_filename' => 'Full_Curriculum_Roadmap',
            ],
            'curriculum' => [
                'type' => 'curriculum',
                'label' => 'Curriculum Syllabus',
                'path' => $application->curriculum_path,
                'default_filename' => 'Curriculum_Syllabus',
            ],
            'cv' => [
                'type' => 'cv',
                'label' => 'Curriculum Vitae (CV)',
                'path' => $application->cv_path,
                'default_filename' => 'Curriculum_Vitae',
            ],
        ];

        return array_filter($docs, fn ($doc) => filled($doc['path']));
    }

    /**
     * Download a specific attachment file for an instructor application.
     */
    public function downloadSingleAttachment(InstructorApplication $application, string $type): Response|StreamedResponse|null
    {
        $resolved = $this->resolveAttachment($application, $type);

        if (! $resolved || ! $resolved['exists']) {
            Notification::make()
                ->title('Attachment not found')
                ->body('The requested attached document could not be located on the server.')
                ->warning()
                ->send();

            return null;
        }

        $applicantSlug = Str::slug($application->name ?: 'Applicant', '_');
        $ext = pathinfo($resolved['path'], PATHINFO_EXTENSION);
        $extSuffix = $ext ? '.' . $ext : '.pdf';
        $downloadName = "{$applicantSlug}_{$resolved['default_filename']}{$extSuffix}";

        return Storage::disk('public')->download($resolved['normalized_path'], $downloadName);
    }

    /**
     * Download all attached documents for a single application bundled into a ZIP archive.
     */
    public function downloadApplicationAttachmentsZip(InstructorApplication $application): ?StreamedResponse
    {
        return $this->downloadMultipleApplicationsAttachmentsZip(
            collect([$application]),
            'Instructor_Attachments_' . Str::slug($application->name ?: 'Applicant', '_')
        );
    }

    /**
     * Download all attached roadmaps, curricula, and CVs across multiple applications into a structured ZIP.
     *
     * @param Collection<int, InstructorApplication>|array $applications
     */
    public function downloadMultipleApplicationsAttachmentsZip(Collection|array $applications, string $zipNamePrefix = 'Instructor_Attached_Documents'): ?StreamedResponse
    {
        $records = is_array($applications) ? collect($applications) : $applications;

        if ($records->isEmpty()) {
            Notification::make()->title('No applications selected')->warning()->send();

            return null;
        }

        $tempDir = storage_path('app/temp');
        if (! is_dir($tempDir)) {
            @mkdir($tempDir, 0755, true);
        }

        $zipFileName = $zipNamePrefix . '_' . now()->format('Ymd_His') . '.zip';
        $tempZipPath = $tempDir . '/' . uniqid('instructor_docs_', true) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($tempZipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            Notification::make()->title('Unable to create ZIP archive on server')->danger()->send();

            return null;
        }

        $disk = Storage::disk('public');
        $filesAdded = 0;
        $isSingleApplication = $records->count() === 1;

        foreach ($records as $application) {
            if (! $application instanceof InstructorApplication) {
                continue;
            }

            $applicantSlug = Str::slug($application->name ?: 'Applicant', '_');
            $idStr = str_pad((string) $application->id, 4, '0', STR_PAD_LEFT);
            $folderPrefix = $isSingleApplication ? '' : "{$applicantSlug}_ID{$idStr}/";

            $attachments = $this->getAttachmentsList($application);
            $appFilesAdded = 0;

            foreach ($attachments as $type => $info) {
                $normalized = PublicDiskPath::normalize($info['path']);

                if ($normalized && $disk->exists($normalized)) {
                    $ext = pathinfo($normalized, PATHINFO_EXTENSION);
                    $extSuffix = $ext ? '.' . $ext : '.pdf';
                    $entryName = $folderPrefix . $info['default_filename'] . $extSuffix;

                    // Read content safely (supports both local and S3 storage)
                    $fileContents = $disk->get($normalized);
                    $zip->addFromString($entryName, $fileContents);
                    $filesAdded++;
                    $appFilesAdded++;
                }
            }

            // Add a summary readme file for this application inside the archive
            $readmeText = "INSTRUCTOR APPLICATION ATTACHMENTS\n";
            $readmeText .= "===================================\n";
            $readmeText .= "Applicant Name:   " . ($application->name ?: 'N/A') . "\n";
            $readmeText .= "Email:            " . ($application->email ?: 'N/A') . "\n";
            $readmeText .= "Phone:            " . ($application->phone ?: 'N/A') . "\n";
            $readmeText .= "WhatsApp:         " . ($application->whatsapp ?: 'N/A') . "\n";
            $readmeText .= "Proposal Type:    " . ($application->proposal_type ?: 'legacy') . "\n";
            if ($application->proposal_type === 'existing') {
                $readmeText .= "Preferred Course: " . ($application->preferredCourse?->title ?? ('ID #' . $application->preferred_course_id)) . "\n";
            } else {
                $readmeText .= "Proposed Course:  " . ($application->proposed_course_name ?: 'N/A') . " (" . ($application->proposed_course_code ?: 'N/A') . ")\n";
            }
            $readmeText .= "Application Date: " . ($application->created_at?->toDateTimeString() ?? 'N/A') . "\n";
            $readmeText .= "Review Status:    " . ($application->status ?: 'pending') . "\n\n";

            $readmeText .= "ATTACHED FILES STATUS:\n";
            $readmeText .= "----------------------\n";
            foreach ($attachments as $type => $info) {
                $normalized = PublicDiskPath::normalize($info['path']);
                $found = $normalized && $disk->exists($normalized);
                $readmeText .= "- " . $info['label'] . ": " . ($found ? 'Included' : 'Not found on server storage') . " (" . $info['path'] . ")\n";
            }

            $zip->addFromString($folderPrefix . 'APPLICATION_DETAILS.txt', $readmeText);
            $filesAdded++;
        }

        $zip->close();

        if ($filesAdded === 0) {
            if (file_exists($tempZipPath)) {
                @unlink($tempZipPath);
            }

            Notification::make()
                ->title('No attached documents found')
                ->body('None of the selected applications have attached Roadmaps, Curricula, or CVs on storage.')
                ->warning()
                ->send();

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
     * Resolve single attachment record details.
     */
    protected function resolveAttachment(InstructorApplication $application, string $type): ?array
    {
        $normalizedType = match (Str::lower($type)) {
            'roadmap', 'roadmap_path' => 'roadmap',
            'full_roadmap', 'full_roadmap_path', 'fullroadmap' => 'full_roadmap',
            'curriculum', 'curriculum_path' => 'curriculum',
            'cv', 'cv_path' => 'cv',
            default => null,
        };

        if (! $normalizedType) {
            return null;
        }

        $rawPath = match ($normalizedType) {
            'roadmap' => $application->roadmap_path,
            'full_roadmap' => $application->full_roadmap_path,
            'curriculum' => $application->curriculum_path,
            'cv' => $application->cv_path,
        };

        if (blank($rawPath)) {
            return null;
        }

        $normalizedPath = PublicDiskPath::normalize($rawPath);
        $disk = Storage::disk('public');
        $exists = $normalizedPath && $disk->exists($normalizedPath);

        $defaultFilename = match ($normalizedType) {
            'roadmap' => 'Course_Roadmap',
            'full_roadmap' => 'Full_Curriculum_Roadmap',
            'curriculum' => 'Curriculum_Syllabus',
            'cv' => 'Curriculum_Vitae',
        };

        return [
            'type' => $normalizedType,
            'raw_path' => $rawPath,
            'normalized_path' => $normalizedPath,
            'path' => $normalizedPath ?: $rawPath,
            'exists' => $exists,
            'default_filename' => $defaultFilename,
        ];
    }
}
