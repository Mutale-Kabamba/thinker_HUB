<?php

namespace App\Ai\Tools\Public;

use App\Models\Course;
use App\Models\CourseIntake;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchPublicCoursesTool implements Tool
{
    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search active courses in the think.er HUB public course catalog by keyword, level, or category. Returns title, code, fees/tuition, duration, delivery mode, and upcoming/active intake dates.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()->nullable(),
            'level' => $schema->string()->nullable(),
            'category' => $schema->string()->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $keyword = trim((string) ($request['keyword'] ?? ''));
        $level = trim((string) ($request['level'] ?? ''));
        $category = trim((string) ($request['category'] ?? ''));

        $query = Course::query()
            ->where('is_active', true)
            ->with(['intakes' => function ($q) {
                $q->where('status', '!=', CourseIntake::STATUS_ARCHIVED)
                    ->orderBy('start_date');
            }]);

        if ($keyword !== '') {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%")
                    ->orWhere('overview', 'like', "%{$keyword}%");
            });
        }

        if ($level !== '') {
            $query->where('level_progression', 'like', "%{$level}%");
        }

        $courses = $query->limit(10)->get();

        if ($courses->isEmpty()) {
            return 'No active courses matching your criteria were found in the catalog.';
        }

        $results = [];

        foreach ($courses as $course) {
            $intakesInfo = [];
            foreach ($course->intakes as $intake) {
                $startDate = $intake->start_date ? $intake->start_date->format('M d, Y') : 'TBA';
                $intakesInfo[] = "{$intake->name} (Starts: {$startDate}, Status: {$intake->status})";
            }

            $intakesText = ! empty($intakesInfo) ? implode('; ', $intakesInfo) : 'Rolling admission / Open enrollment';

            $results[] = [
                'id' => $course->id,
                'title' => $course->title,
                'code' => $course->code,
                'fees' => $course->fees ? (string) $course->fees : 'Free / Included',
                'timeline' => $course->timeline ?? 'Self-paced',
                'offering_mode' => $course->offering_mode ?? 'once_off',
                'open_enrollment' => (bool) $course->is_open_enrollment,
                'for_kids' => (bool) $course->is_for_kids,
                'description' => $course->description ? substr(strip_tags($course->description), 0, 200) . '...' : null,
                'intakes' => $intakesText,
            ];
        }

        return json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

