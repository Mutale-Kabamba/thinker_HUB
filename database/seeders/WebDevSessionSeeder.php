<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseSession;
use Illuminate\Database\Seeder;

class WebDevSessionSeeder extends Seeder
{
    public function run(): void
    {
        $course = Course::query()->firstOrCreate(
            ['code' => 'WEB101'],
            [
                'title' => 'Introduction to Web Development',
                'description' => 'A beginner-friendly course covering HTML, CSS, and JavaScript fundamentals.',
                'is_active' => true,
            ]
        );

        $jsonPath = public_path('samples/imports/WEB101.json');
        if (! file_exists($jsonPath)) {
            $this->command->error("File not found: {$jsonPath}");
            return;
        }

        $sessions = json_decode(file_get_contents($jsonPath), true);
        if (! is_array($sessions)) {
            $this->command->error("Invalid JSON content in {$jsonPath}");
            return;
        }

        CourseSession::query()->where('course_id', $course->id)->delete();

        foreach ($sessions as $item) {
            CourseSession::query()->create([
                'course_id' => $course->id,
                'title' => $item['title'],
                'session_date' => $item['session_date'],
                'start_time' => $item['start_time'],
                'end_time' => $item['end_time'],
                'type' => $item['type'] ?? 'group',
                'status' => $item['status'] ?? 'scheduled',
                'notes' => $item['notes'] ?? null,
            ]);
        }

        $this->command->info("Seeded " . count($sessions) . " sessions for Introduction to Web Development (WEB101).");
    }
}
