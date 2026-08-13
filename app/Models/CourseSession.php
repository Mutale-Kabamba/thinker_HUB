<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function isGroup(): bool
    {
        return $this->type === 'group';
    }

    public function isOneOnOne(): bool
    {
        return $this->type === 'one_on_one';
    }

    public function getEffectiveDate(): Carbon
    {
        $date = $this->status === 'rescheduled' && $this->rescheduled_date
            ? $this->rescheduled_date
            : $this->session_date;

        if ($date instanceof Carbon) {
            return $date;
        }

        if (is_string($date) && filled($date)) {
            try {
                return Carbon::parse($date);
            } catch (\Throwable) {
                return Carbon::today();
            }
        }

        return Carbon::today();
    }

    public function getEffectiveStartTime(): string
    {
        $time = $this->status === 'rescheduled' && $this->rescheduled_start_time
            ? $this->rescheduled_start_time
            : $this->start_time;

        return filled($time) ? (string) $time : '00:00:00';
    }

    public function getEffectiveEndTime(): string
    {
        $time = $this->status === 'rescheduled' && $this->rescheduled_end_time
            ? $this->rescheduled_end_time
            : $this->end_time;

        return filled($time) ? (string) $time : '23:59:59';
    }

    public function effectiveStartAt(): Carbon
    {
        $date = $this->getEffectiveDate();
        $time = $this->getEffectiveStartTime();

        try {
            return $date->copy()->setTimeFromTimeString($time);
        } catch (\Throwable) {
            try {
                return Carbon::parse($date->format('Y-m-d') . ' ' . $time);
            } catch (\Throwable) {
                return $date->copy()->startOfDay();
            }
        }
    }

    public function effectiveEndAt(): Carbon
    {
        $date = $this->getEffectiveDate();
        $time = $this->getEffectiveEndTime();

        try {
            return $date->copy()->setTimeFromTimeString($time);
        } catch (\Throwable) {
            try {
                return Carbon::parse($date->format('Y-m-d') . ' ' . $time);
            } catch (\Throwable) {
                return $date->copy()->endOfDay();
            }
        }
    }
}
