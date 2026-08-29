<?php

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\XpTransaction;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $transactions = XpTransaction::query()
            ->whereNull('course_id')
            ->get();

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
                    // Ignore subject lookup errors
                }
            }

            // 2. Fallback to user's first/active enrollment
            if ($courseId === null && $tx->user_id) {
                $enrollment = Enrollment::query()->where('user_id', $tx->user_id)->first();
                if ($enrollment) {
                    $courseId = $enrollment->course_id;
                    $intakeId = $enrollment->course_intake_id;
                }
            }

            if ($courseId !== null) {
                $tx->course_id = $courseId;
                $tx->course_intake_id = $intakeId;
                $tx->saveQuietly();
            }
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
