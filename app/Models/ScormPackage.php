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

    protected static function booted(): void
    {
        static::deleting(function (ScormPackage $package): void {
            $lessonIds = Lesson::query()
                ->where('type', 'scorm')
                ->where(function ($q) use ($package): void {
                    $q->where('scorm_package_id', $package->id);
                    if ($package->lesson_id) {
                        $q->orWhere('id', $package->lesson_id);
                    }
                })
                ->pluck('id');

            if ($lessonIds->isNotEmpty()) {
                LessonProgress::query()->whereIn('lesson_id', $lessonIds)->delete();
                Lesson::query()->whereIn('id', $lessonIds)->delete();
            }

            $sectionIds = is_array($package->manifest_meta['section_ids'] ?? null)
                ? $package->manifest_meta['section_ids']
                : [];
            if (! empty($sectionIds)) {
                CourseSection::query()
                    ->whereIn('id', $sectionIds)
                    ->whereDoesntHave('lessons')
                    ->delete();
            }

            if (filled($package->storage_path)) {
                try {
                    Storage::disk($package->storage_disk ?: 'public')->deleteDirectory($package->storage_path);
                } catch (\Throwable) {
                    // Best-effort storage cleanup
                }
            }
        });
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
