<?php

namespace App\Ai\Tools\Instructor;

use App\Models\Course;
use App\Models\LearningMaterial;
use App\Models\Lesson;
use App\Models\ResourceVideo;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchCourseCurriculumTool implements Tool
{
    public function __construct(protected User $instructor, protected array $allowedCourseIds)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search and explore course syllabus sections, lessons, downloadable learning materials (slides, cheat sheets, manuals), and resource videos across authorized courses.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'course_id' => $schema->integer()->description('Optional course ID to focus curriculum lookup on.')->nullable(),
            'keyword' => $schema->string()->description('Optional search term to filter lessons and learning materials.')->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $courseId = ! empty($request['course_id']) ? (int) $request['course_id'] : null;
        $keyword = trim((string) ($request['keyword'] ?? ''));

        $activeCourseIds = $this->allowedCourseIds;
        if ($courseId !== null) {
            if (! $this->isAuthorizedCourse($courseId)) {
                return "Error: Unauthorized. You cannot inspect curriculum for course ID #{$courseId}.";
            }
            $activeCourseIds = [$courseId];
        }

        if (empty($activeCourseIds) && ! $this->instructor->isAdmin()) {
            return 'Notice: You currently have no authorized courses assigned.';
        }

        $coursesQuery = Course::query()
            ->where(function ($q) use ($activeCourseIds) {
                if (! $this->instructor->isAdmin()) {
                    $q->whereIn('id', $activeCourseIds);
                }
            })
            ->with([
                'sections.lessons' => function ($q) use ($keyword) {
                    if ($keyword !== '') {
                        $q->where('title', 'like', "%{$keyword}%")
                            ->orWhere('description', 'like', "%{$keyword}%");
                    }
                },
                'learningMaterials' => function ($q) use ($keyword) {
                    if ($keyword !== '') {
                        $q->where('title', 'like', "%{$keyword}%")
                            ->orWhere('category', 'like', "%{$keyword}%")
                            ->orWhere('description', 'like', "%{$keyword}%");
                    }
                },
                'resourceVideos' => function ($q) use ($keyword) {
                    if ($keyword !== '') {
                        $q->where('title', 'like', "%{$keyword}%")
                            ->orWhere('description', 'like', "%{$keyword}%");
                    }
                },
            ]);

        $courses = $coursesQuery->limit(5)->get();

        if ($courses->isEmpty()) {
            return 'No courses or matching curriculum found for the specified criteria.';
        }

        $curriculum = [];

        foreach ($courses as $c) {
            $sectionsData = [];
            foreach ($c->sections as $sec) {
                $lessonsData = [];
                foreach ($sec->lessons as $les) {
                    $lessonsData[] = [
                        'lesson_id' => $les->id,
                        'title' => $les->title,
                        'duration' => $les->duration,
                        'is_free' => (bool) $les->is_free,
                    ];
                }
                $sectionsData[] = [
                    'section_id' => $sec->id,
                    'title' => $sec->title,
                    'lessons_count' => count($lessonsData),
                    'lessons' => $lessonsData,
                ];
            }

            $materialsData = [];
            foreach ($c->learningMaterials as $mat) {
                $materialsData[] = [
                    'material_id' => $mat->id,
                    'title' => $mat->title,
                    'category' => $mat->category,
                    'type' => $mat->type ?? 'document',
                    'file_path' => $mat->file_path,
                ];
            }

            $videosData = [];
            foreach ($c->resourceVideos as $vid) {
                $videosData[] = [
                    'video_id' => $vid->id,
                    'title' => $vid->title,
                    'url' => $vid->url,
                ];
            }

            $curriculum[] = [
                'course_id' => $c->id,
                'course_title' => $c->title,
                'course_code' => $c->code,
                'category' => $c->category,
                'level' => $c->level,
                'sections' => $sectionsData,
                'learning_materials_count' => count($materialsData),
                'learning_materials' => array_slice($materialsData, 0, 10),
                'resource_videos_count' => count($videosData),
                'resource_videos' => array_slice($videosData, 0, 10),
            ];
        }

        return json_encode([
            'total_courses_analyzed' => count($curriculum),
            'courses' => $curriculum,
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

