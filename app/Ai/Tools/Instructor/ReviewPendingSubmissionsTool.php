<?php

namespace App\Ai\Tools\Instructor;

use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Course;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ReviewPendingSubmissionsTool implements Tool
{
    public function __construct(protected User $instructor, protected array $allowedCourseIds)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Inspect student assignment submissions awaiting instructor review across authorized courses, or submit grades, qualitative feedback, and optional resubmission allowances.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_id' => $schema->integer()->description('Optional course ID to filter submissions.')->nullable(),
            'submission_id' => $schema->integer()->description('Specific assignment submission ID if grading or viewing a single submission.')->nullable(),
            'status' => $schema->string()->description('Filter status: "pending" (unreviewed), "graded", or "all". Default is "pending".')->nullable(),
            'grade' => $schema->string()->description('Numerical grade percentage or score (0-100) to assign when grading a submission.')->nullable(),
            'feedback' => $schema->string()->description('Detailed feedback, commentary, and evaluation notes for the student.')->nullable(),
            'grant_retake' => $schema->boolean()->description('Whether to permit the student to submit a revision/retake.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $submissionId = ! empty($request['submission_id']) ? (int) $request['submission_id'] : null;
        $courseId = ! empty($request['course_id']) ? (int) $request['course_id'] : null;
        $statusFilter = strtolower(trim((string) ($request['status'] ?? 'pending')));
        $grade = $request['grade'] ?? null;
        $feedback = isset($request['feedback']) ? trim((string) $request['feedback']) : null;
        $grantRetake = $request['grant_retake'] ?? null;

        // 1. If grading a specific submission:
        if ($submissionId !== null && ($grade !== null || $feedback !== null || $grantRetake !== null)) {
            $submission = AssignmentSubmission::query()
                ->with(['assignment.course', 'user'])
                ->find($submissionId);

            if (! $submission) {
                return "Error: Assignment submission #{$submissionId} does not exist.";
            }

            $subCourseId = $submission->assignment?->course_id;
            if (! $subCourseId || ! $this->isAuthorizedCourse($subCourseId)) {
                return "Error: Unauthorized. You cannot grade submission #{$submissionId} for unauthorized course ID #{$subCourseId}.";
            }

            $updates = [];
            if ($grade !== null) {
                $cleanGrade = is_numeric($grade) ? (string) min(100, max(0, (float) $grade)) : (string) $grade;
                $updates['grade'] = $cleanGrade;
                $updates['status'] = 'Graded';
            }

            if ($feedback !== null) {
                $updates['feedback'] = $feedback;
            }

            if ($grantRetake !== null) {
                if ($grantRetake) {
                    $submission->grantRetake($this->instructor);
                } else {
                    $submission->revokeRetake();
                }
            }

            if (! empty($updates)) {
                $submission->update($updates);
            }

            return json_encode([
                'success' => true,
                'message' => "Successfully updated submission #{$submissionId}.",
                'submission' => [
                    'id' => $submission->id,
                    'student' => $submission->user?->name,
                    'assignment' => $submission->assignment?->name,
                    'grade' => $submission->fresh()->grade,
                    'feedback' => $submission->fresh()->feedback,
                    'status' => $submission->fresh()->status,
                    'retake_allowed' => (bool) $submission->fresh()->retake_allowed,
                ],
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }

        // 2. Listing submissions:
        $activeCourseIds = $this->allowedCourseIds;
        if ($courseId !== null) {
            if (! $this->isAuthorizedCourse($courseId)) {
                return "Error: Unauthorized. You cannot view submissions for course ID #{$courseId}.";
            }
            $activeCourseIds = [$courseId];
        }

        if (empty($activeCourseIds) && ! $this->instructor->isAdmin()) {
            return 'Notice: You currently have no authorized courses assigned.';
        }

        $query = AssignmentSubmission::query()
            ->whereHas('assignment', function ($q) use ($activeCourseIds) {
                if (! $this->instructor->isAdmin()) {
                    $q->whereIn('course_id', $activeCourseIds);
                }
            })
            ->with(['assignment.course:id,title,code', 'user:id,name,email']);

        if ($submissionId !== null) {
            $query->where('id', $submissionId);
        } elseif ($statusFilter === 'pending') {
            $query->where(function ($q) {
                $q->whereNull('grade')
                    ->orWhere('status', 'Submitted')
                    ->orWhere('status', 'resubmitted');
            });
        } elseif ($statusFilter === 'graded') {
            $query->whereNotNull('grade');
        }

        $submissions = $query->orderByDesc('submitted_at')->limit(20)->get();

        if ($submissions->isEmpty()) {
            return "No {$statusFilter} assignment submissions found in your authorized courses.";
        }

        $list = [];
        foreach ($submissions as $sub) {
            $list[] = [
                'submission_id' => $sub->id,
                'assignment_id' => $sub->assignment_id,
                'assignment_title' => $sub->assignment?->name,
                'course_title' => $sub->assignment?->course?->title,
                'student_id' => $sub->user_id,
                'student_name' => $sub->user?->name,
                'student_email' => $sub->user?->email,
                'submitted_at' => $sub->submitted_at?->format('Y-m-d H:i') ?? $sub->created_at->format('Y-m-d H:i'),
                'status' => $sub->status ?? ($sub->grade ? 'Graded' : 'Submitted'),
                'grade' => $sub->grade,
                'feedback' => $sub->feedback,
                'retake_allowed' => (bool) $sub->retake_allowed,
                'text_content_preview' => $sub->content ? mb_substr(strip_tags($sub->content), 0, 120) : null,
                'link' => $sub->link,
                'files_count' => count($sub->all_file_paths),
            ];
        }

        return json_encode([
            'total_found' => count($list),
            'filter' => $statusFilter,
            'submissions' => $list,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }

    protected function isAuthorizedCourse(int $courseId): bool
    {
        if ($this->instructor->isAdmin()) {
            return true;
        }

        return in_array($courseId, $this->allowedCourseIds, true);
    }
}

