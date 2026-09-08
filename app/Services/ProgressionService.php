<?php

namespace App\Services;

use App\Events\CourseCompleted;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\LessonProgress;
use App\Models\User;

class ProgressionService
{
    /**
     * Initializes default progression when a user enrolls in a course.
     */
    public function initializeCourseProgress(User $user, Course $course): void
    {
        $lessons = $course->lessons()->orderBy('order_column', 'asc')->get();

        foreach ($lessons as $index => $lesson) {
            LessonProgress::firstOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                [
                    'course_id' => $course->id,
                    'is_unlocked' => $index === 0, // First item unlocked by default
                    'is_completed' => false,
                ]
            );
        }
    }

    /**
     * Determines if a lesson is accessible by the student.
     */
    public function isLessonUnlocked(User $user, Lesson $lesson): bool
    {
        if ($lesson->is_free_preview) {
            return true;
        }

        $progress = LessonProgress::where('user_id', $user->id)
            ->where('lesson_id', $lesson->id)
            ->first();

        return (bool) ($progress?->is_unlocked);
    }

    /**
     * Marks the active lesson as complete and unlocks the immediate successor.
     */
    public function completeAndAdvance(User $user, Lesson $currentLesson): ?Lesson
    {
        // 1. Mark current lesson complete
        LessonProgress::updateOrCreate(
            ['user_id' => $user->id, 'lesson_id' => $currentLesson->id],
            [
                'course_id' => $currentLesson->course_id,
                'is_unlocked' => true,
                'is_completed' => true,
                'completed_at' => now(),
            ]
        );

        // 2. Fetch the next sequential lesson
        $nextLesson = Lesson::where('course_id', $currentLesson->course_id)
            ->where('order_column', '>', $currentLesson->order_column)
            ->orderBy('order_column', 'asc')
            ->first();

        if ($nextLesson) {
            LessonProgress::updateOrCreate(
                ['user_id' => $user->id, 'lesson_id' => $nextLesson->id],
                [
                    'course_id' => $currentLesson->course_id,
                    'is_unlocked' => true,
                ]
            );
        }

        // 3. Recalculate enrollment completion percentage
        $this->updateCourseEnrollmentProgress($user, $currentLesson->course);

        return $nextLesson;
    }

    /**
     * Calculates and updates total enrollment progress.
     */
    public function updateCourseEnrollmentProgress(User $user, Course $course): void
    {
        $totalLessons = $course->lessons()->count();
        if ($totalLessons === 0) {
            return;
        }

        $completedLessons = LessonProgress::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->where('is_completed', true)
            ->count();

        $percentage = (int) round(($completedLessons / $totalLessons) * 100);

        $enrollment = $user->enrollments()->where('course_id', $course->id)->first();
        if ($enrollment) {
            $wasAlreadyCompleted = $enrollment->completed_at !== null;
            $enrollment->update([
                'progress_percentage' => $percentage,
                'completed_at' => $percentage >= 100 ? ($enrollment->completed_at ?? now()) : null,
            ]);

            if ($percentage >= 100 && ! $wasAlreadyCompleted) {
                event(new CourseCompleted($user, $course));

                // If gamification service exists, award course completion XP if configured
                try {
                    $gamification = app(\App\Services\GamificationService::class);
                    if (method_exists($gamification, 'awardCourseCompletionXp')) {
                        $gamification->awardCourseCompletionXp($user, $course);
                    }
                } catch (\Throwable) {
                    // Fail gracefully if gamification service is not configured
                }
            }
        }
    }
}
