<?php

namespace App\Filament\Student\Pages;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\User;
use App\Notifications\StudentSubmissionNotification;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Carbon;

class Assignments extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?int $navigationSort = 3;

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
        $assignment = Assignment::query()->visibleTo($user)->whereKey($assignmentId)->first();
        if (! $assignment) {
            Notification::make()->title('Assignment not available.')->danger()->send();
            return;
        }
        $draft = $this->submissionDrafts[$assignmentId] ?? [];
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
        AssignmentSubmission::query()->updateOrCreate(
            [
                'assignment_id' => $assignmentId,
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
            fn (User $admin) => $admin->notify(new StudentSubmissionNotification($user->name, 'assignment', $assignment->name, $assignment->id))
        );
        Notification::make()->title('Assignment submitted.')->success()->send();
        $this->refreshAssignments();
    }

    public function removeSubmission(int $assignmentId): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        AssignmentSubmission::query()
            ->where('assignment_id', $assignmentId)
            ->where('user_id', $user->id)
            ->delete();

        Notification::make()->title('Submission deleted.')->success()->send();
        $this->refreshAssignments();
    }

    protected function refreshAssignments(): void
    {
        $user = auth()->user();

        if (! $user) {
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
            ->orderByRaw('due_date is null')
            ->orderBy('due_date')
            ->get()
            ->map(fn (Assignment $item) => [
                'course' => $item->course?->title ?? 'Unassigned course',
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description ?? '',
                'file_path' => $item->file_path,
                'scope' => $scopeLabels[$item->scope] ?? ucfirst($item->scope),
                'due' => optional($item->due_date)?->format('Y-m-d') ?? 'No due date',
                'status' => $submissions->get($item->id)?->status ?? 'Not submitted',
                'submitted_at' => optional($submissions->get($item->id)?->submitted_at)?->format('Y-m-d H:i') ?: '-',
                'submission' => [
                    'text' => $submissions->get($item->id)?->content ?? '',
                    'file' => $submissions->get($item->id)?->file_path ?? null,
                    'link' => $submissions->get($item->id)?->link ?? null,
                    'video' => $submissions->get($item->id)?->video_url ?? null,
                ],
                'grade' => $submissions->get($item->id)?->grade,
                'feedback' => $submissions->get($item->id)?->feedback,
            ])
            ->values()
            ->all();

        foreach ($this->assignments as $assignment) {
            $this->submissionDrafts[$assignment['id']] = $assignment['submission'];
        }
    }
}
