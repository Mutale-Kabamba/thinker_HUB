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
        'file_paths',
        'link',
        'video_url',
        'status',
        'grade',
        'feedback',
        'submitted_at',
        'viewed_at',
        'is_retake',
        'retake_allowed',
        'retake_granted_at',
        'retake_granted_by',
        'raw_grade',
    ];

    protected function casts(): array
    {
        return [
            'file_paths' => 'array',
            'submitted_at' => 'datetime',
            'viewed_at' => 'datetime',
            'is_retake' => 'boolean',
            'retake_allowed' => 'boolean',
            'retake_granted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (AssignmentSubmission $submission) {
            $rawPaths = $submission->attributes['file_paths'] ?? null;
            if (is_string($rawPaths)) {
                $rawPaths = json_decode($rawPaths, true);
            }

            if (is_array($rawPaths) && ! empty($rawPaths)) {
                $clean = array_values(array_filter($rawPaths, fn ($p) => filled($p)));
                $submission->attributes['file_paths'] = json_encode($clean);
                if (! empty($clean)) {
                    $submission->attributes['file_path'] = $clean[0];
                }
            } elseif (! empty($submission->attributes['file_path'])) {
                $submission->attributes['file_paths'] = json_encode([$submission->attributes['file_path']]);
            }

            if ($submission->is_retake && $submission->grade !== null && is_numeric($submission->grade)) {
                $numGrade = (float) $submission->grade;
                if ($numGrade >= 50) {
                    $submission->raw_grade = (string) $numGrade;
                    $submission->grade = '50';
                }
            }
        });
    }

    public function getFilePathsAttribute($value): array
    {
        if ($value !== null) {
            $decoded = is_string($value) ? json_decode($value, true) : $value;
            if (is_array($decoded) && ! empty($decoded)) {
                return array_values(array_filter($decoded, fn ($p) => filled($p)));
            }
        }

        if (filled($this->file_path)) {
            return [$this->file_path];
        }

        return [];
    }

    /**
     * @return array<int, string>
     */
    public function getAllFilePathsAttribute(): array
    {
        return $this->file_paths;
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

    public function isViewed(): bool
    {
        return $this->viewed_at !== null;
    }

    public function markAsViewed(): void
    {
        if ($this->viewed_at === null) {
            $this->updateQuietly(['viewed_at' => now()]);
        }
    }
}
