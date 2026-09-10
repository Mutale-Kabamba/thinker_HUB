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

    public const STATUS_APOLOGY = 'apology';

    public const STATUSES = [
        self::STATUS_PRESENT,
        self::STATUS_ABSENT,
        self::STATUS_LATE,
        self::STATUS_APOLOGY,
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
     * sessions, otherwise students enrolled in the session's course (respecting
     * intake scoping when specified).
     *
     * @return array{total_eligible: int, newly_created: int, student_ids: list<int>}
     */
    public static function syncForSession(CourseSession $session): array
    {
        $studentIds = [];

        if ($session->student_id && $session->isOneOnOne()) {
            $studentIds = [(int) $session->student_id];
        } elseif ($session->course_id) {
            $query = Enrollment::query()->where('course_id', $session->course_id);

            if ($session->course_intake_id) {
                $intakeStudentIds = (clone $query)
                    ->where('course_intake_id', $session->course_intake_id)
                    ->pluck('user_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();

                if (! empty($intakeStudentIds)) {
                    $studentIds = $intakeStudentIds;
                } else {
                    $studentIds = $query->pluck('user_id')
                        ->map(fn ($id) => (int) $id)
                        ->all();
                }
            } else {
                $studentIds = $query->pluck('user_id')
                    ->map(fn ($id) => (int) $id)
                    ->all();
            }
        }

        $studentIds = array_values(array_unique($studentIds));
        $newlyCreated = 0;

        foreach ($studentIds as $studentId) {
            try {
                $record = self::query()->firstOrCreate([
                    'course_session_id' => $session->id,
                    'user_id' => $studentId,
                ]);

                if ($record->wasRecentlyCreated) {
                    $newlyCreated++;
                }
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return [
            'total_eligible' => count($studentIds),
            'newly_created' => $newlyCreated,
            'student_ids' => $studentIds,
        ];
    }
}
