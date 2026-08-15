<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssignmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assignment_id',
        'user_id',
        'content',
        'file_path',
        'link',
        'video_url',
        'status',
        'grade',
        'feedback',
        'submitted_at',
        'is_retake',
        'retake_allowed',
        'retake_granted_at',
        'retake_granted_by',
        'raw_grade',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'datetime',
            'is_retake' => 'boolean',
            'retake_allowed' => 'boolean',
            'retake_granted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (AssignmentSubmission $submission) {
            if ($submission->is_retake && $submission->grade !== null && is_numeric($submission->grade)) {
                $numGrade = (float) $submission->grade;
                if ($numGrade >= 50) {
                    $submission->raw_grade = (string) $numGrade;
                    $submission->grade = '50';
                }
            }
        });
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(Assignment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function retakeGrantedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'retake_granted_by');
    }

    public function canRetake(): bool
    {
        return (bool) $this->retake_allowed;
    }

    public function grantRetake(User $instructor): void
    {
        $this->update([
            'retake_allowed' => true,
            'status' => 'resubmit_allowed',
            'retake_granted_at' => now(),
            'retake_granted_by' => $instructor->id,
        ]);
    }

    public function revokeRetake(): void
    {
        $this->update([
            'retake_allowed' => false,
            'status' => $this->grade !== null ? 'Graded' : 'Submitted',
            'retake_granted_at' => null,
            'retake_granted_by' => null,
        ]);
    }
}
