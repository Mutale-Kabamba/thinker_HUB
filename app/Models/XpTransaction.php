<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class XpTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'course_id',
        'course_intake_id',
        'amount_xp',
        'amount_coins',
        'activity_type',
        'subject_type',
        'subject_id',
        'points',
        'source',
        'source_id',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'course_id' => 'integer',
            'course_intake_id' => 'integer',
            'amount_xp' => 'integer',
            'amount_coins' => 'integer',
            'points' => 'integer',
            'subject_id' => 'integer',
            'source_id' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function intake(): BelongsTo
    {
        return $this->belongsTo(CourseIntake::class, 'course_intake_id');
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
