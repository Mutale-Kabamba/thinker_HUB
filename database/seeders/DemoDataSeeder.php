<?php

namespace Database\Seeders;

use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\LearningMaterial;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoDataSeeder extends Seeder
{
    /**
     * Seed dashboard demo data.
     */
    public function run(): void
    {
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'track' => 'Advanced',
                'email_verified_at' => now(),
            ]
        );

        $beginner = User::query()->updateOrCreate(
            ['email' => 'aisha@example.com'],
            [
                'name' => 'Aisha Bello',
                'password' => Hash::make('password'),
                'role' => 'student',
                'track' => 'Beginner',
                'email_verified_at' => now(),
            ]
        );

        $advanced = User::query()->updateOrCreate(
            ['email' => 'david@example.com'],
            [
                'name' => 'David Okoro',
                'password' => Hash::make('password'),
                'role' => 'student',
                'track' => 'Advanced',
                'email_verified_at' => now(),
            ]
        );

        Assignment::query()->updateOrCreate(
            ['name' => 'Sales cleaning challenge'],
            [
                'description' => 'Clean and standardize the sales dataset.',
                'scope' => 'level',
                'target_track' => 'Beginner',
                'due_date' => now()->addDays(7)->toDateString(),
            ]
        );

        Assignment::query()->updateOrCreate(
            ['name' => 'SQL profiling task'],
            [
                'description' => 'Analyze performance of sample SQL queries.',
                'scope' => 'personal',
                'target_user_id' => $advanced->id,
                'due_date' => now()->addDays(14)->toDateString(),
            ]
        );

        Assignment::query()->updateOrCreate(
            ['name' => 'Dashboard KPI summary'],
            [
                'description' => 'Build KPI summary visuals from cleaned data.',
                'scope' => 'all',
                'due_date' => now()->addDays(21)->toDateString(),
            ]
        );

        LearningMaterial::query()->updateOrCreate(
            ['title' => 'Intro dataset'],
            [
                'material_type' => 'File',
                'scope' => 'all',
                'file_name' => 'sales_data.csv',
            ]
        );

        LearningMaterial::query()->updateOrCreate(
            ['title' => 'Regional cleanup worksheet'],
            [
                'material_type' => 'File',
                'scope' => 'level',
                'target_track' => 'Beginner',
                'file_name' => 'messy_regional_data.csv',
            ]
        );

        LearningMaterial::query()->updateOrCreate(
            ['title' => 'Advanced SQL brief'],
            [
                'material_type' => 'File',
                'scope' => 'personal',
                'target_user_id' => $advanced->id,
                'file_name' => 'store_db.sql',
            ]
        );

        Assessment::query()->updateOrCreate(
            ['user_id' => $beginner->id],
            ['name' => 'Beginner Assessment', 'score' => 78]
        );

        Assessment::query()->updateOrCreate(
            ['user_id' => $advanced->id],
            ['name' => 'Advanced Assessment', 'score' => null]
        );
    }
}
