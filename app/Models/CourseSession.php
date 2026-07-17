<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class CourseSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'instructor_id',
        'type',
        'student_id',
        'title',
        'session_date',
        'start_time',
        'end_time',
        'status',
        'live_provider',
        'live_room_code',
        'live_started_at',
        'live_ended_at',
        'live_metadata',
        'rescheduled_date',
        'rescheduled_start_time',
        'rescheduled_end_time',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'session_date' => 'date',
            'rescheduled_date' => 'date',
            'live_started_at' => 'datetime',
            'live_ended_at' => 'datetime',
            'live_metadata' => 'array',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function liveAttendances(): HasMany
    {
        return $this->hasMany(LiveSessionAttendance::class, 'course_session_id');
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function isOneOnOne(): bool
    {
        return $this->type === 'one_on_one';
    }

    public function getEffectiveDate(): \Illuminate\Support\Carbon
    {
        return $this->status === 'rescheduled' && $this->rescheduled_date
            ? $this->rescheduled_date
            : $this->session_date;
    }

    public function getEffectiveStartTime(): string
    {
        return $this->status === 'rescheduled' && $this->rescheduled_start_time
            ? $this->rescheduled_start_time
            : $this->start_time;
    }

    public function getEffectiveEndTime(): string
    {
        return $this->status === 'rescheduled' && $this->rescheduled_end_time
            ? $this->rescheduled_end_time
            : $this->end_time;
    }

    public function effectiveStartAt(): \Illuminate\Support\Carbon
    {
        return $this->getEffectiveDate()->copy()->setTimeFromTimeString($this->getEffectiveStartTime());
    }

    public function effectiveEndAt(): \Illuminate\Support\Carbon
    {
        return $this->getEffectiveDate()->copy()->setTimeFromTimeString($this->getEffectiveEndTime());
    }

    public function canUserStartLive(User $user): bool
    {
        if ($user->isAdmin() || (int) $this->instructor_id === (int) $user->id) {
            return true;
        }

        if ($user->isInstructor()) {
            return $user->instructorCourses()->where('courses.id', $this->course_id)->exists();
        }

        return false;
    }

    public function isUserParticipant(User $user): bool
    {
        if ($this->canUserStartLive($user)) {
            return true;
        }

        if ($this->isOneOnOne()) {
            return (int) $this->student_id === (int) $user->id;
        }

        return $user->courses()->where('courses.id', $this->course_id)->exists();
    }

    public function canUserJoinLive(User $user): bool
    {
        if (! $this->isUserParticipant($user)) {
            return false;
        }

        if ($this->canUserStartLive($user)) {
            return true;
        }

        if (! $this->live_started_at || $this->live_ended_at) {
            return false;
        }

        $now = now();
        $windowStart = $this->effectiveStartAt()->copy()->subMinutes(30);
        $windowEnd = $this->effectiveEndAt()->copy()->addHours(3);

        return $now->between($windowStart, $windowEnd);
    }

    public function ensureLiveRoomCode(): string
    {
        if ($this->live_room_code) {
            return $this->live_room_code;
        }

        $date = $this->getEffectiveDate()->format('Ymd');
        $courseCode = $this->course?->code ?: 'course';

        $code = Str::slug($courseCode . '-' . $date . '-' . $this->id);

        $this->forceFill(['live_room_code' => $code])->save();

        return $code;
    }

    public function recordingUrl(): ?string
    {
        return data_get($this->live_metadata, 'recording_url');
    }

    public function breakoutRooms(): array
    {
        $rooms = data_get($this->live_metadata, 'breakouts', []);

        return is_array($rooms) ? array_values(array_filter($rooms, fn ($name) => is_string($name) && trim($name) !== '')) : [];
    }
}
