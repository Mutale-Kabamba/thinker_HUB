<?php

namespace App\Filament\Student\Pages;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Support\PublicDiskPath;
use App\Models\User;
use App\Notifications\StudentSubmissionNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class Assignments extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static string|\UnitEnum|null $navigationGroup = 'EVALUATIONS';

    protected static ?int $navigationSort = 1;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        try {
            $submittedIds = AssignmentSubmission::query()
                ->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->whereNull('retake_allowed')
                        ->orWhere('retake_allowed', false);
                })
                ->where(function ($q) {
                    $q->whereNotNull('submitted_at')
                        ->orWhereIn('status', ['Submitted', 'Graded', 'Checked', 'Pending']);
                })
                ->pluck('assignment_id');

            $pendingCount = Assignment::query()
                ->visibleTo($user)
                ->released()
                ->whereNotIn('id', $submittedIds)
                ->count();

            return $pendingCount > 0 ? (string) $pendingCount : null;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pending assignments';
    }

    protected string $view = 'filament.student.pages.assignments';

    public array $assignments = [];

    public array $submissionDrafts = [];

    public function mount(): void
    {
        $this->refreshAssignments();
    }

    public function submit(int $assignmentId): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }
        $assignment = Assignment::query()->visibleTo($user)->released()->whereKey($assignmentId)->first();
        if (! $assignment) {
            Notification::make()->title('Assignment not available.')->danger()->send();

            return;
        }

        $existing = AssignmentSubmission::query()
            ->where('assignment_id', $assignmentId)
            ->where('user_id', $user->id)
            ->first();

        $canResubmit = $existing && (bool) $existing->retake_allowed;

        if ($existing && ! $canResubmit && ($existing->grade !== null || in_array($existing->status, ['Graded', 'Checked'], true))) {
            Notification::make()->title('This assignment has already been graded and cannot be resubmitted.')->warning()->send();

            return;
        }

        $draft = $this->submissionDrafts[$assignmentId] ?? [];
        $content = trim((string) ($draft['text'] ?? ''));
        $link = isset($draft['link']) ? trim((string) $draft['link']) : null;
        $video = isset($draft['video']) ? trim((string) $draft['video']) : null;
        
        $filePaths = [];
        if (isset($draft['files']) && is_array($draft['files'])) {
            foreach ($draft['files'] as $file) {
                if (is_object($file) && method_exists($file, 'store')) {
                    $filePaths[] = $file->store('submissions', 'public');
                } elseif (is_string($file) && filled($file)) {
                    $filePaths[] = PublicDiskPath::normalize($file);
                }
            }
        }

        if (isset($draft['file']) && $draft['file']) {
            $file = $draft['file'];
            if (is_object($file) && method_exists($file, 'store')) {
                $filePaths[] = $file->store('submissions', 'public');
            } elseif (is_string($file) && filled($file)) {
                $filePaths[] = PublicDiskPath::normalize($file);
            }
        }

        $filePaths = array_values(array_unique(array_filter($filePaths)));

        if (empty($filePaths) && $existing) {
            $filePaths = $existing->all_file_paths;
        }

        $primaryFilePath = ! empty($filePaths) ? $filePaths[0] : null;
        $isRetake = $canResubmit || (bool) $existing?->is_retake;

        AssignmentSubmission::query()->updateOrCreate(
            [
                'assignment_id' => $assignmentId,
                'user_id' => $user->id,
            ],
            [
                'content' => $content,
                'file_path' => $primaryFilePath,
                'file_paths' => ! empty($filePaths) ? $filePaths : null,
                'link' => $link,
                'video_url' => $video,
                'status' => 'Submitted',
                'submitted_at' => Carbon::now(),
                'is_retake' => $isRetake,
                'retake_allowed' => false,
            ],
        );
        User::query()->where('role', 'admin')->get()->each(function (User $admin) use ($user, $assignment): void {
            try {
                $admin->notify(new StudentSubmissionNotification($user->name, 'assignment', $assignment->name, $assignment->id));
            } catch (Throwable $exception) {
                // Mail failures should not block the student submission flow.
                Log::warning('Failed to notify admin about assignment submission.', [
                    'admin_id' => $admin->id,
                    'admin_email' => $admin->email,
                    'assignment_id' => $assignment->id,
                    'student_id' => $user->id,
                    'error' => $exception->getMessage(),
                ]);
            }
        });
        Notification::make()->title($isRetake ? 'Revised assignment submitted successfully!' : 'Assignment submitted.')->success()->send();
        $this->refreshAssignments();
    }

    public function removeSubmission(int $assignmentId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $existing = AssignmentSubmission::query()
            ->where('assignment_id', $assignmentId)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && ! $existing->retake_allowed && ($existing->grade !== null || in_array($existing->status, ['Graded', 'Checked'], true))) {
            Notification::make()->title('Graded submissions cannot be deleted.')->warning()->send();

            return;
        }

        AssignmentSubmission::query()
            ->where('assignment_id', $assignmentId)
            ->where('user_id', $user->id)
            ->delete();

        Notification::make()->title('Submission deleted.')->success()->send();
        $this->refreshAssignments();
    }

    public function downloadFile(int $assignmentId, ?int $fileIndex = null): ?StreamedResponse
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $assignment = Assignment::query()->visibleTo($user)->released()->whereKey($assignmentId)->first();

        if (! $assignment) {
            Notification::make()->title('Assignment not found.')->danger()->send();

            return null;
        }

        $paths = $assignment->all_file_paths;
        $rawPath = $fileIndex !== null && isset($paths[$fileIndex]) ? $paths[$fileIndex] : ($paths[0] ?? $assignment->file_path);

        if (! $rawPath) {
            Notification::make()->title('File not found.')->danger()->send();

            return null;
        }

        $path = PublicDiskPath::normalize($rawPath);

        if (! $path || ! Storage::disk('public')->exists($path)) {
            Notification::make()->title('Attachment is missing from storage.')->danger()->send();

            return null;
        }

        return Storage::disk('public')->download($path, Str::afterLast($path, '/'));
    }

    public function downloadSubmissionFile(int $assignmentId, ?int $fileIndex = null): ?StreamedResponse
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $submission = AssignmentSubmission::query()
            ->where('assignment_id', $assignmentId)
            ->where('user_id', $user->id)
            ->first();

        if (! $submission) {
            Notification::make()->title('Submission not found.')->danger()->send();

            return null;
        }

        $paths = $submission->all_file_paths;
        $rawPath = $fileIndex !== null && isset($paths[$fileIndex]) ? $paths[$fileIndex] : ($paths[0] ?? $submission->file_path);

        if (! $rawPath) {
            Notification::make()->title('Submission file not found.')->danger()->send();

            return null;
        }

        $path = PublicDiskPath::normalize($rawPath);

        if (! $path || ! Storage::disk('public')->exists($path)) {
            Notification::make()->title('Submission attachment is missing from storage.')->danger()->send();

            return null;
        }

        return Storage::disk('public')->download($path, Str::afterLast($path, '/'));
    }

    protected function refreshAssignments(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->assignments = [];

            return;
        }

        $scopeLabels = [
            'all' => 'General',
            'level' => 'Level',
            'personal' => 'Personal',
        ];

        $submissions = AssignmentSubmission::query()
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('assignment_id');

        $this->assignments = Assignment::query()
            ->with('course')
            ->visibleTo($user)
            ->released()
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->get()
            ->map(function (Assignment $item) use ($submissions, $scopeLabels): array {
                $sub = $submissions->get($item->id);
                $retakeAllowed = $sub && (bool) $sub->retake_allowed;
                $isGraded = $sub && ! $retakeAllowed && ($sub->grade !== null || in_array($sub->status, ['Graded', 'Checked'], true));

                return [
                    'course' => $item->course?->title ?? 'Unassigned course',
                    'id' => $item->id,
                    'name' => $item->name,
                    'description' => $item->description ?? '',
                    'file_path' => $item->file_path,
                    'file_paths' => $item->all_file_paths,
                    'scope' => $scopeLabels[$item->scope] ?? ucfirst($item->scope),
                    'due' => optional($item->due_date)?->format('Y-m-d') ?? 'No due date',
                    'status' => $retakeAllowed ? '2nd Try Available' : ($sub?->status ?? 'Not submitted'),
                    'submitted_at' => optional($sub?->submitted_at)?->format('Y-m-d H:i') ?: '-',
                    'is_graded' => $isGraded,
                    'retake_allowed' => $retakeAllowed,
                    'is_retake' => (bool) $sub?->is_retake,
                    'submission' => [
                        'id' => $sub?->id,
                        'text' => $sub?->content ?? '',
                        'file' => $sub?->file_path ?? null,
                        'files' => $sub?->all_file_paths ?? [],
                        'link' => $sub?->link ?? null,
                        'video' => $sub?->video_url ?? null,
                    ],
                    'grade' => $sub?->grade,
                    'feedback' => $sub?->feedback,
                ];
            })
            ->values()
            ->all();

        foreach ($this->assignments as $assignment) {
            $this->submissionDrafts[$assignment['id']] = $assignment['submission'];
        }
    }
}
