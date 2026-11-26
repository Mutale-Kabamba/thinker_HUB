<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class ScormPackage extends Model
{
    use BelongsToTenant;
    use HasFactory;

    public const STANDARD_SCORM_12 = 'scorm_12';
    public const STANDARD_SCORM_2004 = 'scorm_2004';
    public const STANDARD_XAPI = 'xapi';

    protected $fillable = [
        'tenant_id',
        'course_id',
        'lesson_id',
        'title',
        'standard',
        'version',
        'storage_disk',
        'storage_path',
        'entry_point',
        'manifest_identifier',
        'manifest_meta',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'manifest_meta' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }

    public function attempts(): HasMany
    {
        return $this->hasMany(ScormAttempt::class);
    }

    public function getLaunchUrlAttribute(): string
    {
        return route('scorm.content', [
            'package' => $this->id,
            'path' => ltrim($this->entry_point, '/'),
        ]);
    }
}
