<?php

namespace App\Filament\Instructor\Pages;

use App\Filament\Instructor\Concerns\ScopedToInstructor;
use App\Mail\CohortBroadcast;
use App\Models\Broadcast;
use App\Models\Course;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Livewire\WithFileUploads;

class Broadcasts extends Page
{
    use ScopedToInstructor, WithFileUploads;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static string|\UnitEnum|null $navigationGroup = 'COMMUNITY & SYSTEM';

    protected static ?string $navigationLabel = 'Broadcasts';

    protected static ?int $navigationSort = 9;

    protected static ?string $title = 'Cohort Broadcasts';

    protected string $view = 'filament.instructor.pages.broadcasts';

    public string $courseId = '';

    public string $subject = '';

    public string $message = '';

    /** @var mixed */
    public $attachment = null;

    /** @var array<int, array<string, mixed>> */
    public array $courseOptions = [];

    /** @var array<int, array<string, mixed>> */
    public array $history = [];

    public function mount(): void
    {
        $this->loadCourses();
        $this->loadHistory();
    }

    public function loadCourses(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $courseIds = static::instructorCourseIds();

        $this->courseOptions = Course::query()
            ->whereIn('id', $courseIds)
            ->where('is_active', true)
            ->orderBy('title')
            ->get(['id', 'title', 'code'])
            ->map(fn ($course): array => [
                'id' => (string) $course->id,
                'label' => $course->title . ($course->code ? ' (' . $course->code . ')' : ''),
            ])
            ->all();
    }

    public function getEnrolledCountProperty(): int
    {
        $courseId = (int) $this->courseId;

        if ($courseId <= 0) {
            return 0;
        }

        return User::query()
            ->whereHas('enrollments', fn ($q) => $q->where('course_id', $courseId))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->distinct()
            ->count();
    }

    public function removeAttachment(): void
    {
        $this->attachment = null;
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

        // Validate optional attachment if uploaded (Max 25MB)
        if ($this->attachment) {
            $this->validate([
                'attachment' => 'file|max:25600', // 25 MB max
            ]);
        }

        // Server-side guard: verify course belongs to instructor
        $course = Course::query()
            ->whereIn('id', static::instructorCourseIds())
            ->whereKey($courseId)
            ->first();

        if (! $course) {
            Notification::make()
                ->title('You can only broadcast to your own active courses.')
                ->danger()
                ->send();

            return;
        }

        $attachmentPath = null;
        $attachmentName = null;
        $attachmentMime = null;
        $attachmentSize = null;

        if ($this->attachment) {
            $attachmentName = $this->attachment->getClientOriginalName();
            $attachmentMime = $this->attachment->getMimeType();
            $attachmentSize = $this->attachment->getSize();
            $attachmentPath = $this->attachment->store('broadcasts', 'public');
        }

        [$recipients, $failed] = $this->dispatchToCourse(
            $course,
            $user,
            $subject,
            $body,
            $attachmentPath,
            $attachmentName,
            $attachmentMime,
            $attachmentSize
        );

        if ($recipients === 0 && $failed === 0) {
            Notification::make()
                ->title('No enrolled students with email addresses found in this course.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Broadcast sent to ' . $recipients . ' student' . ($recipients === 1 ? '' : 's') . ($failed > 0 ? ' (' . $failed . ' email errors)' : '') . ($attachmentPath ? ' with media attachment' : ''))
            ->success()
            ->send();

        $this->subject = '';
        $this->message = '';
        $this->attachment = null;
        $this->loadHistory();
    }

    /**
     * Send email and in-app database notification to every enrolled student and write the audit row.
     *
     * @return array{0: int, 1: int} [recipients, failed]
     */
    protected function dispatchToCourse(
        Course $course,
        User $sender,
        string $subject,
        string $body,
        ?string $attachmentPath = null,
        ?string $attachmentName = null,
        ?string $attachmentMime = null,
        ?int $attachmentSize = null
    ): array {
        $recipients = 0;
        $failed = 0;

        $students = User::query()
            ->whereHas('enrollments', fn ($q) => $q->where('course_id', $course->id))
            ->whereNotNull('email')
            ->where('email', '!=', '')
            ->distinct()
            ->get();

        $broadcast = Broadcast::query()->create([
            'course_id' => $course->id,
            'user_id' => $sender->id,
            'subject' => $subject,
            'body' => $body,
            'attachment_path' => $attachmentPath,
            'attachment_name' => $attachmentName,
            'attachment_mime' => $attachmentMime,
            'attachment_size' => $attachmentSize,
            'sent_at' => now(),
        ]);

        foreach ($students as $student) {
            $mailSent = false;

            try {
                Mail::to($student->email)->send(new CohortBroadcast(
                    $course,
                    $sender,
                    $body,
                    $subject,
                    $attachmentPath,
                    $attachmentName,
                    $attachmentMime
                ));
                $mailSent = true;
            } catch (\Throwable $e) {
                Log::warning("Cohort broadcast email delivery failed for user #{$student->id} ({$student->email}): " . $e->getMessage());
                $failed++;
            }

            if ($mailSent) {
                $recipients++;
            }

            // Always deliver in-app notification to student dashboard so they see the announcement immediately
            try {
                $notificationBody = Str::limit($body, 180);
                if ($attachmentName) {
                    $notificationBody .= ' 📎 ' . $attachmentName;
                }

                Notification::make()
                    ->title('Announcement: ' . $subject)
                    ->body($notificationBody)
                    ->icon('heroicon-o-megaphone')
                    ->info()
                    ->actions([
                        Action::make('view')
                            ->label('View Dashboard')
                            ->url('/learn')
                            ->markAsRead(),
                    ])
                    ->sendToDatabase($student);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $broadcast->update([
            'recipients_count' => $recipients,
            'failed_count' => $failed,
        ]);

        return [$recipients, $failed];
    }

    protected function loadHistory(): void
    {
        $user = auth()->user();

        if (! $user) {
            return;
        }

        $query = Broadcast::query()
            ->with('course:id,title,code')
            ->latest('sent_at');

        if (! $user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $this->history = $query
            ->limit(30)
            ->get()
            ->map(fn (Broadcast $broadcast): array => [
                'id' => $broadcast->id,
                'course' => $broadcast->course?->title ?? '—',
                'subject' => $broadcast->subject,
                'body' => $broadcast->body,
                'attachment_path' => $broadcast->attachment_path,
                'attachment_name' => $broadcast->attachment_name,
                'attachment_size' => $broadcast->formatted_attachment_size,
                'recipients_count' => $broadcast->recipients_count,
                'failed_count' => $broadcast->failed_count,
                'sent_at' => $broadcast->sent_at?->format('M d, Y H:i') ?? $broadcast->created_at?->format('M d, Y H:i'),
            ])
            ->all();
    }
}
