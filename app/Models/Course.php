<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'code',
        'description',
        'overview',
        'timeline',
        'fees',
        'requirements',
        'key_outcome',
        'level_progression',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function enrolledUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'enrollments')->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    public function materials(): HasMany
    {
        return $this->hasMany(LearningMaterial::class);
    }

    public function assessments(): HasMany
    {
        return $this->hasMany(Assessment::class);
    }

    public function instructors(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_instructor')->withTimestamps();
    }
}
