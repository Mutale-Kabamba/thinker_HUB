<?php

namespace App\Filament\Student\Pages;

use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\User;
use App\Notifications\StudentSubmissionNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Assessments extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-check-badge';

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

        $content = trim((string) ($this->submissionDrafts[$assessmentId] ?? ''));

        AssessmentSubmission::query()->updateOrCreate(
            [
                'assessment_id' => $assessmentId,
                'user_id' => $user->id,
            ],
            [
                'content' => $content,
                'status' => 'Submitted',
                'submitted_at' => Carbon::now(),
            ],
        );

        User::query()->where('role', 'admin')->get()->each(
            fn (User $admin) => $admin->notify(new StudentSubmissionNotification($user->name, 'assessment', 'Assessment #'.$assessment->id, $assessment->id))
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
                'course' => $item->course?->title ?? 'Unassigned course',
                'status' => $item->status,
                'score' => $item->score ?? '-',
                'updated_at' => $item->updated_at?->format('Y-m-d') ?? '-',
                'submission_status' => $submissions->get($item->id)?->status ?? 'Not submitted',
                'submission_content' => $submissions->get($item->id)?->content ?? '',
            ])
            ->values()
            ->all();

        foreach ($this->assessments as $assessment) {
            $this->submissionDrafts[$assessment['id']] = $assessment['submission_content'];
        }
    }
}
