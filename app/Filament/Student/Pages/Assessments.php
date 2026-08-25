<?php

namespace App\Filament\Student\Pages;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Support\PublicDiskPath;
use App\Models\User;
use App\Notifications\StudentSubmissionNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Assessments extends Page
{
    use WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static string|\UnitEnum|null $navigationGroup = 'EVALUATIONS';

    protected static ?int $navigationSort = 3;

    public static function getNavigationBadge(): ?string
    {
        $user = auth()->user();
        if (! $user) {
            return null;
        }

        try {
            $submittedIds = AssessmentSubmission::query()
                ->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->whereNull('retake_allowed')
                        ->orWhere('retake_allowed', false);
                })
                ->where(function ($q) {
                    $q->where(function ($sq) {
                        $sq->whereNotNull('score')->where('score', '!=', '-');
                    })->orWhereIn('status', ['Graded', 'Checked', 'Submitted', 'Pending']);
                })
                ->pluck('assessment_id');

            $pendingCount = Assessment::query()
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
        return 'Pending assessments';
    }

    protected string $view = 'filament.student.pages.assessments';

    public array $assessments = [];

    public array $submissionDrafts = [];

    public function mount(): void
    {
        $this->refreshAssessments();
    }

    public function submit(int $assessmentId): void
    {
        $user = auth()->user();
        if (! $user) {
            return;
        }
        $assessment = Assessment::query()->visibleTo($user)->released()->whereKey($assessmentId)->first();
        if (! $assessment) {
            Notification::make()->title('Assessment not available.')->danger()->send();

            return;
        }

        $existing = AssessmentSubmission::query()
            ->where('assessment_id', $assessmentId)
            ->where('user_id', $user->id)
            ->first();

        $canResubmit = $existing && (bool) $existing->retake_allowed;

        if ($existing && ! $canResubmit && (($existing->score !== null && $existing->score !== '-') || in_array($existing->status, ['Graded', 'Checked'], true))) {
            Notification::make()->title('This assessment has already been graded and cannot be resubmitted.')->warning()->send();

            return;
        }

        $draft = $this->submissionDrafts[$assessmentId] ?? [];
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

        AssessmentSubmission::query()->updateOrCreate(
            [
                'assessment_id' => $assessmentId,
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
        User::query()->where('role', 'admin')->get()->each(
            fn (User $admin) => $admin->notify(new StudentSubmissionNotification($user->name, 'assessment', $assessment->name ?: 'Assessment #'.$assessment->id, $assessment->id))
        );
        Notification::make()->title($isRetake ? 'Revised assessment submitted successfully!' : 'Assessment submitted.')->success()->send();
        $this->refreshAssessments();
    }

    public function removeSubmission(int $assessmentId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $existing = AssessmentSubmission::query()
            ->where('assessment_id', $assessmentId)
            ->where('user_id', $user->id)
            ->first();

        if ($existing && ! $existing->retake_allowed && (($existing->score !== null && $existing->score !== '-') || in_array($existing->status, ['Graded', 'Checked'], true))) {
            Notification::make()->title('Graded submissions cannot be deleted.')->warning()->send();

            return;
        }

        AssessmentSubmission::query()
            ->where('assessment_id', $assessmentId)
            ->where('user_id', $user->id)
            ->delete();

        Notification::make()->title('Submission deleted.')->success()->send();
        $this->refreshAssessments();
    }

    public function downloadFile(int $assessmentId, ?int $fileIndex = null): ?StreamedResponse
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $assessment = Assessment::query()->visibleTo($user)->released()->whereKey($assessmentId)->first();

        if (! $assessment) {
            Notification::make()->title('Assessment not found.')->danger()->send();

            return null;
        }

        $paths = $assessment->all_file_paths;
        $rawPath = $fileIndex !== null && isset($paths[$fileIndex]) ? $paths[$fileIndex] : ($paths[0] ?? $assessment->file_path);

        if (! $rawPath) {
            Notification::make()->title('File not available.')->danger()->send();

            return null;
        }

        $path = PublicDiskPath::normalize($rawPath);
        $disk = Storage::disk('public');

        if (! $path || ! $disk->exists($path)) {
            Notification::make()->title('File not found.')->danger()->send();

            return null;
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $downloadName = Str::slug($assessment->name ?: 'assessment') . '.' . $extension;

        return $disk->download($path, $downloadName);
    }

    public function downloadSubmissionFile(int $assessmentId, ?int $fileIndex = null): ?StreamedResponse
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $submission = AssessmentSubmission::query()
            ->where('assessment_id', $assessmentId)
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
        $disk = Storage::disk('public');

        if (! $path || ! $disk->exists($path)) {
            Notification::make()->title('Submission attachment is missing from storage.')->danger()->send();

            return null;
        }

        return $disk->download($path, Str::afterLast($path, '/'));
    }

    protected function refreshAssessments(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $submissions = AssessmentSubmission::query()->where('user_id', $user->id)->get()->keyBy('assessment_id');

        $this->assessments = Assessment::query()
            ->with('course')
            ->visibleTo($user)
            ->released()
            ->latest()
            ->get()
            ->map(function (Assessment $item) use ($submissions): array {
                $sub = $submissions->get($item->id);
                $score = $sub?->score ?? $item->score ?? '-';
                $retakeAllowed = $sub && (bool) $sub->retake_allowed;
                $isGraded = $sub && ! $retakeAllowed && (($sub->score !== null && $sub->score !== '-') || in_array($sub->status, ['Graded', 'Checked'], true));

                return [
                    'id' => $item->id,
                    'name' => $item->name ?: 'Assessment',
                    'description' => $item->description ?? '',
                    'course' => $item->course?->title ?? 'Unassigned course',
                    'file_path' => $item->file_path,
                    'file_paths' => $item->all_file_paths,
                    'score' => $score,
                    'due_date' => $item->due_date?->format('Y-m-d') ?? '-',
                    'updated_at' => $item->updated_at?->format('Y-m-d') ?? '-',
                    'submission_status' => $retakeAllowed ? '2nd Try Available' : ($sub?->status ?? 'Not submitted'),
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
                    'feedback' => $sub?->feedback,
                ];
            })
            ->values()
            ->all();

        foreach ($this->assessments as $assessment) {
            $this->submissionDrafts[$assessment['id']] = $assessment['submission'];
        }
    }
}
