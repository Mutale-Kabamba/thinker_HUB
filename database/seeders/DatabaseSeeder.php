<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'role' => 'admin',
            'track' => 'Beginner',
        ]);

        User::factory()->create([
            'name' => 'Student User',
            'email' => 'student@example.com',
            'role' => 'student',
            'track' => 'Intermediate',
        ]);

        Course::create([
            'title' => 'Introduction to Web Development',
            'code' => 'WEB101',
            'description' => 'A beginner-friendly course covering HTML, CSS, and JavaScript fundamentals.',
            'is_active' => true,
        ]);
    }
}
