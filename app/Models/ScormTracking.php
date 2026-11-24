<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScormTracking extends Model
{
    use HasFactory;

    protected $table = 'scorm_tracking';

    protected $fillable = [
        'scorm_attempt_id',
        'element',
        'value',
        'raw_payload',
        'recorded_at',
    ];

    protected function casts(): array
    {
        return [
            'raw_payload' => 'array',
            'recorded_at' => 'datetime',
        ];
    }

    public function attempt(): BelongsTo
    {
        return $this->belongsTo(ScormAttempt::class, 'scorm_attempt_id');
    }
}
