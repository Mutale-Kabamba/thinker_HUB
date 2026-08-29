<?php

use App\Models\AssignmentSubmission;
use App\Models\AssessmentSubmission;
use App\Models\Attendance;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\ResourceVideo;
use App\Models\Review;
use App\Models\XpTransaction;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $transactions = XpTransaction::all();

        foreach ($transactions as $tx) {
            $courseId = null;
            $intakeId = null;

            // 1. Try to infer from subject
            if ($tx->subject_type && $tx->subject_id) {
                try {
                    $subject = $tx->subject;
                    if ($subject) {
                        if ($subject instanceof Course) {
                            $courseId = $subject->id;
                        } elseif ($subject instanceof Enrollment) {
                            $courseId = $subject->course_id;
                            $intakeId = $subject->course_intake_id;
                        } elseif ($subject instanceof Review) {
                            if ($subject->reviewable_type === Course::class || $subject->reviewable_type === 'App\Models\Course') {
                                $courseId = (int) $subject->reviewable_id;
                            }
                        } elseif (isset($subject->course_id)) {
                            $courseId = $subject->course_id;
                            $intakeId = $subject->course_intake_id ?? null;
                        } elseif (method_exists($subject, 'quiz') && $subject->quiz) {
                            $courseId = $subject->quiz->course_id;
                            $intakeId = $subject->quiz->course_intake_id;
                        } elseif (method_exists($subject, 'assignment') && $subject->assignment) {
                            $courseId = $subject->assignment->course_id;
                            $intakeId = $subject->assignment->course_intake_id;
                        } elseif (method_exists($subject, 'assessment') && $subject->assessment) {
                            $courseId = $subject->assessment->course_id;
                            $intakeId = $subject->assessment->course_intake_id;
                        } elseif (method_exists($subject, 'courseSession') && $subject->courseSession) {
                            $courseId = $subject->courseSession->course_id;
                            $intakeId = $subject->courseSession->course_intake_id;
                        }
                    }
                } catch (\Throwable $e) {
                    // Ignore
                }
            }

            // 2. Check by activity type if subject was not morph-loaded
            if ($courseId === null && $tx->subject_id) {
                if (in_array($tx->activity_type, ['quiz_passed', 'quiz_perfect', 'quiz_attempt'])) {
                    $q = Quiz::find($tx->subject_id);
                    if ($q) {
                        $courseId = $q->course_id;
                        $intakeId = $q->course_intake_id;
                    }
                } elseif (in_array($tx->activity_type, ['assignment_passed', 'assignment_distinction', 'assignment_perfect', 'assignment_ontime', 'assignment_early'])) {
                    $sub = AssignmentSubmission::find($tx->subject_id);
                    if ($sub && $sub->assignment) {
                        $courseId = $sub->assignment->course_id;
                        $intakeId = $sub->assignment->course_intake_id;
                    }
                } elseif (in_array($tx->activity_type, ['assessment_passed', 'assessment_distinction', 'assessment_perfect', 'assessment_ontime', 'assessment_early'])) {
                    $sub = AssessmentSubmission::find($tx->subject_id);
                    if ($sub && $sub->assessment) {
                        $courseId = $sub->assessment->course_id;
                        $intakeId = $sub->assessment->course_intake_id;
                    }
                } elseif (in_array($tx->activity_type, ['attendance_present', 'perfect_attendance'])) {
                    $att = Attendance::find($tx->subject_id);
                    if ($att && $att->session) {
                        $courseId = $att->session->course_id;
                        $intakeId = $att->session->course_intake_id;
                    }
                } elseif (in_array($tx->activity_type, ['video_watched', 'lesson_video_completed'])) {
                    $vid = ResourceVideo::find($tx->subject_id);
                    if ($vid) {
                        $courseId = $vid->course_id;
                        $intakeId = $vid->course_intake_id;
                    }
                } elseif (in_array($tx->activity_type, ['material_read', 'material_completed'])) {
                    $mat = LearningMaterial::find($tx->subject_id);
                    if ($mat) {
                        $courseId = $mat->course_id;
                        $intakeId = $mat->course_intake_id;
                    }
                }
            }

            // 3. For general platform activities (daily logins, streaks, buddy connections), leave course_id = null
            // so getXpForCourse can attribute general XP dynamically to each active enrollment timeline.
            $isGeneralPlatformActivity = in_array($tx->activity_type, [
                'daily_login',
                'streak_7_milestone',
                'streak_30_milestone',
                'friend_connected',
                'profile_completed',
                'badge',
            ]);

            if ($isGeneralPlatformActivity && $courseId === null) {
                $tx->course_id = null;
                $tx->course_intake_id = null;
            } else {
                $tx->course_id = $courseId;
                $tx->course_intake_id = $intakeId;
            }

            $tx->saveQuietly();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op
    }
};
