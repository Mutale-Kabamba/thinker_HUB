<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'track' => 'Beginner',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'student@example.com'],
            [
                'name' => 'Student User',
                'password' => Hash::make('password'),
                'role' => 'student',
                'track' => 'Intermediate',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        Course::query()->firstOrCreate(
            ['code' => 'WEB101'],
            [
                'title' => 'Introduction to Web Development',
                'description' => 'A beginner-friendly course covering HTML, CSS, and JavaScript fundamentals.',
                'is_active' => true,
            ]
        );

        $this->call([
            ClaimItemSeeder::class,
            CourseGamificationRuleSeeder::class,
            PromoCodeSeeder::class,
            ComprehensiveTestDataSeeder::class,
        ]);
    }
}
