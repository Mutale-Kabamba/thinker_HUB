<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'bio',
        'qualifications',
        'experience',
        'linkedin_url',
        'portfolio_url',
        'cv_path',
        'course_concept_note',
        'proposed_curriculum',
        'preferred_course_id',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
        'user_id',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function preferredCourse(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'preferred_course_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }
}
