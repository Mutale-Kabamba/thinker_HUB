<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
}
