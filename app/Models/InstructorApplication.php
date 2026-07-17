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
        'proficiency',
        'occupation',
        'whatsapp',
        'qualifications',
        'experience',
        'linkedin_url',
        'facebook_url',
        'portfolio_url',
        'cv_path',
        'proposal_type',
        'motivation_note',
        'competence_note',
        'roadmap_path',
        'proposed_course_name',
        'proposed_course_code',
        'proposed_course_description',
        'proposed_course_overview',
        'proposed_course_timeline',
        'proposed_course_fees',
        'proposed_course_requirements',
        'proposed_course_level_progression',
        'proposed_course_key_outcome',
        'proposed_course_is_open_enrollment',
        'teaching_location',
        'full_roadmap_path',
        'curriculum_path',
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
            'proposed_course_is_open_enrollment' => 'boolean',
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
