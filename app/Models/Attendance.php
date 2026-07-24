<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    use HasFactory;

    public const STATUS_PRESENT = 'present';

    public const STATUS_ABSENT = 'absent';

    public const STATUS_LATE = 'late';

    public const STATUSES = [
        self::STATUS_PRESENT,
        self::STATUS_ABSENT,
        self::STATUS_LATE,
    ];

    protected $fillable = [
        'course_session_id',
        'user_id',
        'status',
        'notes',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(CourseSession::class, 'course_session_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Idempotently ensure an attendance row exists for every student who
     * should attend the given session: the assigned student for one-on-one
     * sessions, otherwise every student enrolled in the session's course.
     */
    public static function syncForSession(CourseSession $session): void
    {
        $studentIds = [];

        if ($session->student_id && $session->isOneOnOne()) {
            $studentIds = [(int) $session->student_id];
        } elseif ($session->course_id) {
            $studentIds = Enrollment::query()
                ->where('course_id', $session->course_id)
                ->pluck('user_id')
                ->all();
        }

        foreach ($studentIds as $studentId) {
            try {
                self::query()->firstOrCreate([
                    'course_session_id' => $session->id,
                    'user_id' => $studentId,
                ]);
            } catch (\Throwable $e) {
                report($e);
            }
        }
    }
}
