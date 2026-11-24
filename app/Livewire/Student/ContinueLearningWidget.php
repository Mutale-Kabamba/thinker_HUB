<?php

namespace App\Livewire\Student;

use App\Models\Certificate;
use App\Models\Enrollment;
use App\Models\LessonProgress;
use App\Models\QuizAttempt;
use App\Models\ScormAttempt;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class ContinueLearningWidget extends Component
{
    public function render()
    {
        $user = Auth::user();

        if (! $user) {
            return view('livewire.student.continue-learning-widget', [
                'courseCards' => [],
                'recentScores' => [],
                'certificates' => collect(),
            ]);
        }

        $enrollments = Enrollment::query()
            ->where('user_id', $user->id)
            ->with(['course.lessons'])
            ->latest('updated_at')
            ->limit(4)
            ->get();

        $courseCards = [];
        foreach ($enrollments as $enrollment) {
            $course = $enrollment->course;
            if (! $course) {
                continue;
            }

            $nextProgress = Schema::hasTable('lesson_progress')
                ? LessonProgress::query()
                    ->where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('is_unlocked', true)
                    ->where('is_completed', false)
                    ->with('lesson')
                    ->orderByDesc('updated_at')
                    ->first()
                : null;

            $resumeLesson = $nextProgress?->lesson ?? $course->lessons->sortBy('order_column')->first();
            $resumeUrl = $course->slug
                ? route('course.classroom', ['course' => $course->slug, 'lessonId' => $resumeLesson?->id])
                : null;

            $courseCards[] = [
                'id' => $course->id,
                'title' => $course->title,
                'progress' => (int) ($enrollment->progress_percentage ?? 0),
                'resume_lesson_title' => $resumeLesson?->title,
                'resume_url' => $resumeUrl,
                'bundle_url' => route('offline.course.bundle', $course),
                'is_completed' => $enrollment->completed_at !== null || (int) ($enrollment->progress_percentage ?? 0) >= 100,
            ];
        }

        $recentScores = [];
        if (Schema::hasTable('quiz_attempts')) {
            foreach (QuizAttempt::query()->where('user_id', $user->id)->with('quiz')->latest()->limit(3)->get() as $qa) {
                $pct = $qa->total_points > 0 ? (int) round(((float) $qa->score / (float) $qa->total_points) * 100) : 0;
                $recentScores[] = [
                    'title' => $qa->quiz?->title ?? 'Quiz',
                    'type' => 'Quiz',
                    'score' => $pct . '%',
                    'passed' => (bool) $qa->passed,
                ];
            }
        }

        if (Schema::hasTable('scorm_attempts')) {
            foreach (ScormAttempt::query()->where('user_id', $user->id)->with('package')->latest()->limit(2)->get() as $sa) {
                $recentScores[] = [
                    'title' => $sa->package?->title ?? 'SCORM Module',
                    'type' => strtoupper((string) ($sa->package?->standard ?? 'SCORM')),
                    'score' => $sa->score_raw !== null ? ((int) round((float) $sa->score_raw) . '%') : ucfirst($sa->lesson_status),
                    'passed' => $sa->isCompletedOrPassed(),
                ];
            }
        }

        $certificates = Schema::hasTable('certificates')
            ? Certificate::query()->where('user_id', $user->id)->with('course')->latest()->limit(3)->get()
            : collect();

        return view('livewire.student.continue-learning-widget', [
            'courseCards' => $courseCards,
            'recentScores' => array_slice($recentScores, 0, 4),
            'certificates' => $certificates,
        ]);
    }
}
