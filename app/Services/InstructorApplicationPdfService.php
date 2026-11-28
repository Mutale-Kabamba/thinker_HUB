<?php

namespace App\Services;

use App\Models\InstructorApplication;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPDF;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InstructorApplicationPdfService
{
    /**
     * Render a single Instructor Application dossier as a DomPDF instance.
     */
    public function renderApplicationPdf(InstructorApplication $application): DomPDF
    {
        $application->loadMissing(['reviewer', 'user', 'preferredCourse']);

        $data = $this->prepareApplicationData($application);

        return Pdf::loadView('reports.instructor-application-pdf', $data)
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    /**
     * Render multiple Instructor Applications in a single batch dossier.
     *
     * @param Collection<int, InstructorApplication> $applications
     */
    public function renderCombinedApplicationsPdf(Collection $applications): DomPDF
    {
        if ($applications instanceof \Illuminate\Database\Eloquent\Collection) {
            $applications->loadMissing(['reviewer', 'user', 'preferredCourse']);
        } else {
            $applications->each(fn ($app) => $app->loadMissing(['reviewer', 'user', 'preferredCourse']));
        }

        $applicationsData = $applications->map(fn (InstructorApplication $app) => $this->prepareApplicationData($app));

        return Pdf::loadView('reports.instructor-applications-batch-pdf', [
            'applicationsData' => $applicationsData,
            'totalCount' => $applications->count(),
            'generated_at' => now()->format('Y-m-d H:i'),
        ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);
    }

    /**
     * Generate standard sanitized filename for the application PDF.
     */
    public function getPdfFilename(InstructorApplication $application): string
    {
        $cleanName = Str::slug($application->name ?: 'Applicant', '_');
        $id = str_pad((string) $application->id, 4, '0', STR_PAD_LEFT);

        return "Instructor_Application_{$cleanName}_ID{$id}_" . date('Ymd') . '.pdf';
    }

    /**
     * Prepare structured application data for PDF rendering.
     */
    public function prepareApplicationData(InstructorApplication $application): array
    {
        return [
            'application' => $application,
            'applicant' => [
                'name' => $application->name,
                'email' => $application->email,
                'phone' => $application->phone ?: 'Not provided',
                'whatsapp' => $application->whatsapp ?: 'Not provided',
                'occupation' => $application->occupation ?: 'Not specified',
                'proficiency' => $application->proficiency ?: 'Not specified',
                'bio' => $application->bio ?: 'No biographical summary provided.',
                'linkedin_url' => $application->linkedin_url,
                'facebook_url' => $application->facebook_url,
                'portfolio_url' => $application->portfolio_url,
            ],
            'qualifications' => $application->qualifications,
            'experience' => $application->experience ?: 'No formal experience statement provided.',
            'proposal' => [
                'type' => $application->proposal_type ?: 'legacy',
                'type_label' => match ($application->proposal_type) {
                    'new' => 'New Course Curriculum Proposal',
                    'existing' => 'Existing Course Application',
                    default => 'Course Proposal (Standard)',
                },
                'preferred_course' => $application->preferredCourse,
                'motivation_note' => $application->motivation_note,
                'competence_note' => $application->competence_note,
                'proposed_course_name' => $application->proposed_course_name,
                'proposed_course_code' => $application->proposed_course_code,
                'teaching_location' => $application->teaching_location ?: 'Not specified',
                'timeline' => $application->proposed_course_timeline ?: 'Not specified',
                'is_open_enrollment' => (bool) $application->proposed_course_is_open_enrollment,
                'description' => $application->proposed_course_description,
                'overview' => $application->proposed_course_overview,
                'requirements' => $application->proposed_course_requirements,
                'key_outcome' => $application->proposed_course_key_outcome,
                'fees_structured' => $this->parseFees($application->proposed_course_fees),
                'fees_raw' => $application->proposed_course_fees,
                'progression_structured' => $this->parseProgression($application->proposed_course_level_progression),
                'progression_raw' => $application->proposed_course_level_progression,
                'concept_note' => $application->course_concept_note,
                'curriculum_note' => $application->proposed_curriculum,
            ],
            'attachments' => $this->parseAttachments($application),
            'review' => [
                'status' => $application->status,
                'status_label' => match ($application->status) {
                    'approved' => 'Approved',
                    'rejected' => 'Rejected',
                    default => 'Pending Review',
                },
                'status_badge_class' => match ($application->status) {
                    'approved' => 'badge-success',
                    'rejected' => 'badge-danger',
                    default => 'badge-warning',
                },
                'reviewer_name' => $application->reviewer?->name ?: 'Not assigned',
                'reviewed_at' => $application->reviewed_at?->format('M d, Y H:i') ?: 'Pending',
                'admin_notes' => $application->admin_notes ?: 'No administrative feedback recorded.',
            ],
            'account' => [
                'exists' => (bool) $application->user,
                'user_id' => $application->user?->id,
                'is_active' => (bool) ($application->user?->is_active ?? false),
                'email_verified' => (bool) ($application->user?->email_verified_at ?? false),
            ],
            'reference_id' => 'THUB-APP-' . str_pad((string) $application->id, 5, '0', STR_PAD_LEFT),
            'generated_at' => now()->format('Y-m-d H:i'),
        ];
    }

    /**
     * Parse and structure the proposed course fee tiers.
     */
    protected function parseFees(?string $rawFees): array
    {
        if (blank($rawFees)) {
            return [];
        }

        // Check if already stored as JSON
        $decoded = json_decode($rawFees, true);
        if (is_array($decoded)) {
            $structured = [];
            foreach ($decoded as $category => $tiers) {
                if (! is_array($tiers)) {
                    continue;
                }
                $catLabel = match (Str::lower($category)) {
                    'one_on_one', 'one-on-one' => '1-on-1 Mentorship',
                    'group' => 'Group Cohort',
                    'self_paced', 'self-paced' => 'Self-Paced Learning',
                    default => Str::headline($category),
                };
                foreach ($tiers as $tier) {
                    $structured[] = [
                        'category' => $catLabel,
                        'level' => $tier['level'] ?? 'All Levels',
                        'amount' => $tier['amount'] ?? 'TBD',
                        'duration' => $tier['duration'] ?? '-',
                    ];
                }
            }
            if (! empty($structured)) {
                return $structured;
            }
        }

        // Check if formatted as lines with '+'
        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $rawFees) ?: [])));
        $structured = [];

        foreach ($lines as $line) {
            $parts = array_values(array_filter(array_map('trim', explode('+', $line))));
            if (count($parts) >= 3) {
                $categoryRaw = Str::lower($parts[0]);
                $catLabel = '1-on-1 Mentorship';
                if (Str::contains($categoryRaw, 'self') || Str::contains($categoryRaw, 'paced')) {
                    $catLabel = 'Self-Paced Learning';
                } elseif (Str::contains($categoryRaw, 'group')) {
                    $catLabel = 'Group Cohort';
                }

                $structured[] = [
                    'category' => $catLabel,
                    'level' => Str::headline($parts[1]),
                    'amount' => $parts[2],
                    'duration' => $parts[3] ?? '-',
                ];
            }
        }

        return $structured;
    }

    /**
     * Parse and structure level progression tiers.
     */
    protected function parseProgression(?string $rawProgression): array
    {
        if (blank($rawProgression)) {
            return [];
        }

        $decoded = json_decode($rawProgression, true);
        if (is_array($decoded)) {
            $structured = [];
            foreach ($decoded as $item) {
                if (is_array($item) && isset($item['level'])) {
                    $structured[] = [
                        'level' => $item['level'],
                        'details' => $item['details'] ?? '',
                    ];
                }
            }
            if (! empty($structured)) {
                return $structured;
            }
        }

        $lines = array_values(array_filter(array_map('trim', preg_split('/\R+/', $rawProgression) ?: [])));
        $structured = [];

        foreach ($lines as $line) {
            $parts = array_values(array_filter(array_map('trim', explode('+', $line))));
            if (count($parts) >= 2) {
                $structured[] = [
                    'level' => Str::headline($parts[0]),
                    'details' => $parts[1],
                ];
            }
        }

        return $structured;
    }

    /**
     * Inspect file attachments and collect metadata.
     */
    protected function parseAttachments(InstructorApplication $application): array
    {
        $docs = [
            'cv' => [
                'title' => 'Curriculum Vitae (CV / Resume)',
                'category' => 'Applicant Credentials',
                'path' => $application->cv_path,
            ],
            'roadmap' => [
                'title' => 'Course Delivery Roadmap',
                'category' => 'Existing Course Proposal',
                'path' => $application->roadmap_path,
            ],
            'full_roadmap' => [
                'title' => 'Full Curriculum Roadmap',
                'category' => 'New Course Proposal',
                'path' => $application->full_roadmap_path,
            ],
            'curriculum' => [
                'title' => 'Curriculum Syllabus Document',
                'category' => 'New Course Proposal',
                'path' => $application->curriculum_path,
            ],
        ];

        $results = [];

        foreach ($docs as $key => $doc) {
            $path = $doc['path'];
            $attached = filled($path);
            $sizeFormatted = null;
            $existsOnDisk = false;

            if ($attached) {
                try {
                    if (Storage::disk('public')->exists($path)) {
                        $existsOnDisk = true;
                        $bytes = Storage::disk('public')->size($path);
                        $sizeFormatted = $this->formatBytes($bytes);
                    }
                } catch (\Throwable) {
                    $existsOnDisk = false;
                }
            }

            $results[$key] = [
                'title' => $doc['title'],
                'category' => $doc['category'],
                'path' => $path,
                'filename' => $path ? basename($path) : null,
                'attached' => $attached,
                'exists_on_disk' => $existsOnDisk,
                'size' => $sizeFormatted,
            ];
        }

        return $results;
    }

    /**
     * Format byte count into human-readable string.
     */
    protected function formatBytes(int $bytes, int $precision = 1): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
