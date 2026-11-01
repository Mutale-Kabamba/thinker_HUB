<?php

namespace App\Livewire;

use App\Models\Attendance;
use App\Models\CourseSession;
use Filament\Notifications\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Component;

class SessionViewer extends Component
{
    public CourseSession $session;

    public bool $attendanceMarked = false;

    public ?string $fileUrl = null;

    public ?string $downloadUrl = null;

    public ?string $youtubeId = null;

    public ?string $embedUrl = null;

    public int $requiredSeconds = 900; // default 15 mins (in seconds)

    public function mount(CourseSession $session): void
    {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $canAccess = $user->isAdmin() || $user->isInstructor() || $user->isEnrolledInCourse($session->course_id);
        if (! $canAccess) {
            abort(403, 'You do not have access to this course session.');
        }

        $this->session = $session;
        $parsed = \App\Support\VideoEmbedParser::parse($session->video_url);
        $this->youtubeId = $parsed['youtube_id'];
        if ($parsed['is_direct_file']) {
            $this->fileUrl = $parsed['embed_url'];
        } else {
            $this->embedUrl = $parsed['embed_url'];
        }

        if ($session->isVideo() && $session->video_file_path) {
            $this->fileUrl = route('file.view', ['type' => 'session-video', 'id' => $session->id], false);
            $this->downloadUrl = route('file.download', ['type' => 'session-video', 'id' => $session->id], false);
        } elseif ($session->isRead() && $session->reading_file_path) {
            $this->fileUrl = route('file.view', ['type' => 'session-reading', 'id' => $session->id], false);
            $this->downloadUrl = route('file.download', ['type' => 'session-reading', 'id' => $session->id], false);
        }

        $requiredMinutes = (int) ($session->required_duration_minutes ?? 15);
        if ($requiredMinutes <= 0) {
            $requiredMinutes = 15;
        }
        $this->requiredSeconds = $requiredMinutes * 60;

        // Check if current user already has attendance recorded as present/late
        $this->checkAttendanceStatus();
    }

    protected function checkAttendanceStatus(): void
    {
        $user = Auth::user();
        if (! $user) {
            $this->attendanceMarked = false;

            return;
        }

        $this->attendanceMarked = Attendance::query()
            ->where('course_session_id', $this->session->id)
            ->where('user_id', $user->id)
            ->whereIn('status', [Attendance::STATUS_PRESENT, Attendance::STATUS_LATE])
            ->exists();
    }

    /**
     * Complete video session and mark attendance on register.
     *
     * @param  array{actualSecondsWatched?: float|int, duration?: float|int, currentTime?: float|int}  $payload
     * @return array<string, mixed>
     */
    public function completeVideoSession(array $payload): array
    {
        $user = Auth::user();
        if (! $user) {
            return [
                'status' => 'unauthenticated',
                'message' => 'Please log in to register completion.',
            ];
        }

        if ($this->attendanceMarked) {
            return [
                'status' => 'already_marked',
                'message' => 'Attendance is already recorded for this session.',
            ];
        }

        $actualSecondsWatched = (float) ($payload['actualSecondsWatched'] ?? 0);
        $totalDuration = (float) ($payload['duration'] ?? 0);

        // Required target in seconds based on teacher setting
        $targetSeconds = (float) $this->requiredSeconds;

        // If the teacher specified a required time, verify student watched at least that amount
        // or if video duration is shorter than target, at least 85% of total video duration
        $minRequired = ($totalDuration > 0 && $totalDuration < $targetSeconds)
            ? ($totalDuration * 0.85)
            : $targetSeconds;

        if ($actualSecondsWatched < $minRequired) {
            $remaining = ceil($minRequired - $actualSecondsWatched);

            return [
                'status' => 'threshold_not_met',
                'message' => "Please watch for at least {$remaining} more seconds to complete session attendance.",
                'remaining_seconds' => $remaining,
            ];
        }

        $this->markAttendancePresent($user);

        return [
            'status' => 'success',
            'message' => 'Session completed! Your attendance has been officially marked on the register.',
        ];
    }

    /**
     * Complete reading session and mark attendance on register.
     *
     * @param  array{activeSeconds?: int|float}  $payload
     * @return array<string, mixed>
     */
    public function completeReadingSession(array $payload): array
    {
        $user = Auth::user();
        if (! $user) {
            return [
                'status' => 'unauthenticated',
                'message' => 'Please log in to register completion.',
            ];
        }

        if ($this->attendanceMarked) {
            return [
                'status' => 'already_marked',
                'message' => 'Attendance is already recorded for this session.',
            ];
        }

        $activeSeconds = (int) ($payload['activeSeconds'] ?? 0);
        $targetSeconds = $this->requiredSeconds;

        if ($activeSeconds < $targetSeconds) {
            $remaining = $targetSeconds - $activeSeconds;

            return [
                'status' => 'threshold_not_met',
                'message' => "Please read actively for at least {$remaining} more seconds to complete session attendance.",
                'remaining_seconds' => $remaining,
            ];
        }

        $this->markAttendancePresent($user);

        return [
            'status' => 'success',
            'message' => 'Reading completed! Your attendance has been officially marked on the register.',
        ];
    }

    protected function markAttendancePresent(\App\Models\User $user): void
    {
        $attendance = Attendance::query()->firstOrNew([
            'course_session_id' => $this->session->id,
            'user_id' => $user->id,
        ]);

        $attendance->status = Attendance::STATUS_PRESENT;
        $attendance->notes = 'Completed ' . ($this->session->isVideo() ? 'video guide' : 'reading material') . ' (' . now()->toDayDateTimeString() . ')';
        $attendance->save();

        $this->attendanceMarked = true;

        try {
            Notification::make()
                ->title('Attendance Marked!')
                ->body("You have completed this session. Your attendance is officially recorded as Present on the register.")
                ->success()
                ->send();
        } catch (\Throwable) {
            // filament notification fallback
        }
    }

    #[Layout('layouts.reader')]
    public function render(): View
    {
        return view('livewire.session-viewer');
    }
}
