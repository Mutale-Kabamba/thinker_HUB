<?php

namespace App\Filament\Student\Pages;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\User;
use App\Notifications\StudentSubmissionNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Assessments extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-check-badge';

    protected static ?int $navigationSort = 4;

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
        $assessment = Assessment::query()->where('user_id', $user->id)->whereKey($assessmentId)->first();
        if (! $assessment) {
            Notification::make()->title('Assessment not available.')->danger()->send();

            return;
        }
        $draft = $this->submissionDrafts[$assessmentId] ?? [];
        $content = trim((string) ($draft['text'] ?? ''));
        $link = isset($draft['link']) ? trim((string) $draft['link']) : null;
        $video = isset($draft['video']) ? trim((string) $draft['video']) : null;
        $filePath = null;
        if (isset($draft['file']) && $draft['file']) {
            $file = $draft['file'];
            if (is_object($file) && method_exists($file, 'store')) {
                $filePath = $file->store('submissions', 'public');
            }
        }
        AssessmentSubmission::query()->updateOrCreate(
            [
                'assessment_id' => $assessmentId,
                'user_id' => $user->id,
            ],
            [
                'content' => $content,
                'file_path' => $filePath,
                'link' => $link,
                'video_url' => $video,
                'status' => 'Submitted',
                'submitted_at' => Carbon::now(),
            ],
        );
        User::query()->where('role', 'admin')->get()->each(
            fn (User $admin) => $admin->notify(new StudentSubmissionNotification($user->name, 'assessment', $assessment->name ?: 'Assessment #'.$assessment->id, $assessment->id))
        );
        Notification::make()->title('Assessment submitted.')->success()->send();
        $this->refreshAssessments();
    }

    public function removeSubmission(int $assessmentId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        AssessmentSubmission::query()
            ->where('assessment_id', $assessmentId)
            ->where('user_id', $user->id)
            ->delete();

        Notification::make()->title('Submission deleted.')->success()->send();
        $this->refreshAssessments();
    }

    public function downloadFile(int $assessmentId): ?StreamedResponse
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        $assessment = Assessment::query()->where('user_id', $user->id)->whereKey($assessmentId)->first();

        if (! $assessment || empty($assessment->file_path)) {
            Notification::make()->title('File not available.')->danger()->send();

            return null;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($assessment->file_path)) {
            Notification::make()->title('File not found.')->danger()->send();

            return null;
        }

        $extension = pathinfo($assessment->file_path, PATHINFO_EXTENSION);
        $downloadName = Str::slug($assessment->name ?: 'assessment') . '.' . $extension;

        return $disk->download($assessment->file_path, $downloadName);
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
            ->where('user_id', $user->id)
            ->latest()
            ->get()
            ->map(fn (Assessment $item): array => [
                'id' => $item->id,
                'name' => $item->name ?: 'Assessment',
                'description' => $item->description ?? '',
                'course' => $item->course?->title ?? 'Unassigned course',
                'file_path' => $item->file_path,
                'score' => $submissions->get($item->id)?->score ?? $item->score ?? '-',
                'due_date' => $item->due_date?->format('Y-m-d') ?? '-',
                'updated_at' => $item->updated_at?->format('Y-m-d') ?? '-',
                'submission_status' => $submissions->get($item->id)?->status ?? 'Not submitted',
                'submission' => [
                    'id' => $submissions->get($item->id)?->id,
                    'id' => $submissions->get($item->id)?->id,
                    'text' => $submissions->get($item->id)?->content ?? '',
                    'file' => $submissions->get($item->id)?->file_path ?? null,
                    'link' => $submissions->get($item->id)?->link ?? null,
                    'video' => $submissions->get($item->id)?->video_url ?? null,
                ],
                'feedback' => $submissions->get($item->id)?->feedback,
            ])
            ->values()
            ->all();

        foreach ($this->assessments as $assessment) {
            $this->submissionDrafts[$assessment['id']] = $assessment['submission'];
        }
    }
}
