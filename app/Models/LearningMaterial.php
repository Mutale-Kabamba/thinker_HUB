<?php

namespace App\Models;

use App\Support\PublicDiskPath;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class LearningMaterial extends Model
{
    use HasFactory;

    public const CATEGORY_CURRICULUM = 'Curriculum';
    public const CATEGORY_STUDY_MATERIAL = 'Study Material';
    public const CATEGORY_QUIZ_PREPS = 'Quiz Preps';
    public const CATEGORY_ANSWER_KITS = 'Answer Kits';
    public const CATEGORY_PROJECT_GUIDES = 'Project Guides';
    public const CATEGORY_CHEAT_SHEETS = 'Cheat Sheets';
    public const CATEGORY_PRACTICE_EXERCISES = 'Practice Exercises';
    public const CATEGORY_PAST_PAPERS = 'Past Papers';
    public const CATEGORY_RULES = 'Rules';
    public const CATEGORY_GENERAL_NOTICES = 'General Notices';
    public const CATEGORY_SUPPLEMENTARY = 'Supplementary Resources';

    public const CATEGORIES = [
        self::CATEGORY_CURRICULUM => 'Curriculum & Syllabus',
        self::CATEGORY_STUDY_MATERIAL => 'Study Material & Lecture Notes',
        self::CATEGORY_QUIZ_PREPS => 'Quiz Preps & Study Guides',
        self::CATEGORY_ANSWER_KITS => 'Answer Kits & Solutions',
        self::CATEGORY_PROJECT_GUIDES => 'Project Guides & Lab Manuals',
        self::CATEGORY_CHEAT_SHEETS => 'Cheat Sheets & Quick References',
        self::CATEGORY_PRACTICE_EXERCISES => 'Practice Exercises & Worksheets',
        self::CATEGORY_PAST_PAPERS => 'Past Papers & Mock Tests',
        self::CATEGORY_RULES => 'Rules & Guidelines',
        self::CATEGORY_GENERAL_NOTICES => 'General Notices & Announcements',
        self::CATEGORY_SUPPLEMENTARY => 'Supplementary & Reading Resources',
    ];

    public static function categoryOptions(): array
    {
        return self::CATEGORIES;
    }

    protected $fillable = [
        'course_id',
        'course_intake_id',
        'title',
        'category',
        'description',
        'material_type',
        'scope',
        'target_track',
        'target_user_id',
        'link_url',
        'video_url',
        'file_name',
        'file_path',
    ];

    protected function filePath(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => PublicDiskPath::normalize($value),
            set: fn ($value) => PublicDiskPath::normalize($value),
        );
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function intake(): BelongsTo
    {
        return $this->belongsTo(CourseIntake::class, 'course_intake_id');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(ResourceComment::class, 'commentable');
    }

    public function bookmarks(): MorphMany
    {
        return $this->morphMany(Bookmark::class, 'bookmarkable');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        $enrolledCourseIds = $user->courses()->pluck('courses.id');

        return $query->where(function (Builder $builder) use ($user): void {
            $builder->where('scope', 'all')
                ->orWhere(function (Builder $q) use ($user): void {
                    $q->where('scope', 'level')->where('target_track', $user->track);
                })
                ->orWhere(function (Builder $q) use ($user): void {
                    $q->where('scope', 'personal')->where('target_user_id', $user->id);
                });
        })->whereIn('course_id', $enrolledCourseIds)
        ->where(function (Builder $builder) use ($user): void {
            $builder->whereNull('course_intake_id')
                ->orWhereIn('course_intake_id', function ($sub) use ($user) {
                    $sub->select('course_intake_id')
                        ->from('enrollments')
                        ->where('user_id', $user->id)
                        ->whereNotNull('course_intake_id');
                });
        });
    }
}
