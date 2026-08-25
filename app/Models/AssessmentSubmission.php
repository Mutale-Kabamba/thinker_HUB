<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssessmentSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'user_id',
        'content',
        'file_path',
        'file_paths',
        'link',
        'video_url',
        'status',
        'score',
        'feedback',
        'submitted_at',
        'viewed_at',
        'is_retake',
        'retake_allowed',
        'retake_granted_at',
        'retake_granted_by',
        'raw_score',
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
        static::saving(function (AssessmentSubmission $submission) {
            $paths = $submission->file_paths;
            if (is_array($paths) && ! empty($paths)) {
                $first = reset($paths);
                if ($first && is_string($first)) {
                    $submission->file_path = $first;
                }
            } elseif ($submission->file_path && empty($submission->file_paths)) {
                $submission->file_paths = [$submission->file_path];
            }

            if ($submission->is_retake && $submission->score !== null && is_numeric($submission->score)) {
                $numScore = (float) $submission->score;
                if ($numScore >= 50) {
                    $submission->raw_score = (string) $numScore;
                    $submission->score = 50;
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

    public function assessment(): BelongsTo
    {
        return $this->belongsTo(Assessment::class);
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
            'status' => $this->score !== null ? 'Graded' : 'Submitted',
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
