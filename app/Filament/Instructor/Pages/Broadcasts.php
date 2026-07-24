<?php

namespace App\Filament\Instructor\Pages;

use App\Mail\CohortBroadcast;
use App\Models\Broadcast;
use App\Models\Enrollment;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Mail;

class Broadcasts extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMUNITY & SYSTEM';

    protected static ?string $navigationLabel = 'Broadcasts';

    protected static ?int $navigationSort = 9;

    protected static ?string $title = 'Cohort Broadcasts';

    protected string $view = 'filament.instructor.pages.broadcasts';

    public string $courseId = '';

    public string $subject = '';

    public string $message = '';

    /** @var array<int, array<string, mixed>> */
    public array $courseOptions = [];

    /** @var array<int, array<string, mixed>> */
    public array $history = [];

    public function mount(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $this->courseOptions = $user->instructorCourses()
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['courses.id', 'courses.title', 'courses.code'])
            ->map(fn ($course): array => [
                'id' => (string) $course->id,
                'label' => $course->title.($course->code ? ' ('.$course->code.')' : ''),
            ])
            ->all();

        $this->loadHistory();
    }

    public function send(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $subject = trim($this->subject);
        $body = trim($this->message);
        $courseId = (int) $this->courseId;

        if ($courseId <= 0 || $subject === '' || mb_strlen($subject) > 255 || $body === '') {
            Notification::make()
                ->title('Please choose a course and enter a subject and message.')
                ->warning()
                ->send();

            return;
        }

        // Server-side guard: only the instructor's own courses.
        $course = $user->instructorCourses()->where('courses.id', $courseId)->first();

        if (! $course) {
            Notification::make()
                ->title('You can only broadcast to your own courses.')
                ->danger()
                ->send();

            return;
        }

        [$recipients, $failed] = $this->dispatchToCourse($course, $user, $subject, $body);

        Notification::make()
            ->title('Broadcast queued for '.$recipients.' student'.($recipients === 1 ? '' : 's').($failed > 0 ? ' ('.$failed.' failed)' : ''))
            ->success()
            ->send();

        $this->subject = '';
        $this->message = '';
        $this->loadHistory();
    }

    /**
     * Queue the mailable to every enrolled student and write the audit row.
     *
     * @return array{0: int, 1: int} [recipients, failed]
     */
    protected function dispatchToCourse($course, $sender, string $subject, string $body): array
    {
        $recipients = 0;
        $failed = 0;

        $broadcast = Broadcast::query()->create([
            'course_id' => $course->id,
            'user_id' => $sender->id,
            'subject' => $subject,
            'body' => $body,
            'sent_at' => now(),
        ]);

        Enrollment::query()
            ->where('course_id', $course->id)
            ->with('user')
            ->chunkById(100, function ($enrollments) use (&$recipients, &$failed, $course, $sender, $subject, $body): void {
                foreach ($enrollments as $enrollment) {
                    $student = $enrollment->user;

                    if (! $student || blank($student->email)) {
                        continue;
                    }

                    try {
                        Mail::to($student)->queue(new CohortBroadcast($course, $sender, $body, $subject));
                        $recipients++;
                    } catch (\Throwable $e) {
                        $failed++;
                        report($e);
                    }
                }
            });

        $broadcast->update([
            'recipients_count' => $recipients,
            'failed_count' => $failed,
        ]);

        return [$recipients, $failed];
    }

    protected function loadHistory(): void
    {
        $user = auth()->user();

        $this->history = Broadcast::query()
            ->with('course:id,title,code')
            ->where('user_id', $user?->id ?? 0)
            ->latest()
            ->limit(30)
            ->get()
            ->map(fn (Broadcast $broadcast): array => [
                'course' => $broadcast->course?->title ?? '—',
                'subject' => $broadcast->subject,
                'recipients_count' => $broadcast->recipients_count,
                'failed_count' => $broadcast->failed_count,
                'sent_at' => $broadcast->sent_at?->format('M d, Y H:i'),
            ])
            ->all();
    }
}
