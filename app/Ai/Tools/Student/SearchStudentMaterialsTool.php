<?php

namespace App\Ai\Tools\Student;

use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Database\Eloquent\Builder;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchStudentMaterialsTool implements Tool
{
    public function __construct(protected User $student)
    {
    }

    /**
     * Get the description of the tool's purpose.
     */
    public function description(): Stringable|string
    {
        return 'Search lecture notes, study materials, quiz preps, cheat sheets, and practice exercises accessible to the student across their enrolled courses.';
    }

    /**
     * Get the tool's schema definition.
     */
    public function schema(JsonSchema $schema): array
    {
        return [
            'keyword' => $schema->string()->nullable(),
            'category' => $schema->string()->nullable(),
        ];
    }

    /**
     * Execute the tool.
     */
    public function handle(Request $request): Stringable|string
    {
        $keyword = trim((string) ($request['keyword'] ?? ''));
        $category = trim((string) ($request['category'] ?? ''));

        $query = LearningMaterial::query()
            ->visibleTo($this->student)
            ->with(['course']);

        if ($keyword !== '') {
            $query->where(function (Builder $q) use ($keyword) {
                $q->where('title', 'like', "%{$keyword}%")
                    ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($category !== '') {
            $query->where('category', 'like', "%{$category}%");
        }

        $materials = $query->limit(10)->get();

        if ($materials->isEmpty()) {
            return 'No learning materials found matching your query for your enrolled courses.';
        }

        $results = [];

        foreach ($materials as $material) {
            $results[] = [
                'id' => $material->id,
                'title' => $material->title,
                'category' => $material->category,
                'course' => $material->course?->title ?? 'General',
                'description' => $material->description ? substr(strip_tags($material->description), 0, 200) : null,
                'has_document' => ! empty($material->file_path),
                'link_url' => $material->link_url,
                'video_url' => $material->video_url,
            ];
        }

        return json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    }
}

