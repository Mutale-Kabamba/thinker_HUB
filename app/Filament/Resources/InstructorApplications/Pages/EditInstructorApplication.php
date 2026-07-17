<?php

namespace App\Filament\Resources\InstructorApplications\Pages;

use App\Filament\Resources\InstructorApplications\InstructorApplicationResource;
use App\Models\Course;
use App\Models\InstructorApplication;
use Filament\Notifications\Notification;
use App\Filament\Resources\Pages\BaseEditRecord;
use Illuminate\Support\Str;

class EditInstructorApplication extends BaseEditRecord
{
    protected static string $resource = InstructorApplicationResource::class;

    protected function afterSave(): void
    {
        /** @var InstructorApplication $application */
        $application = $this->record;

        if ($application->status === 'approved') {
            $user = $application->user;

            if ($user) {
                $user->update([
                    'role' => 'instructor',
                    'is_active' => true,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                    'proficiency' => $application->proficiency ?: $user->proficiency,
                    'occupation' => $application->occupation ?: $user->occupation,
                    'whatsapp' => $application->whatsapp ?: $user->whatsapp,
                    'linkedin_url' => $application->linkedin_url ?: $user->linkedin_url,
                    'facebook_url' => $application->facebook_url ?: $user->facebook_url,
                ]);

                // Assign the preferred course if it's an existing course application
                if ($application->proposal_type === 'existing' && $application->preferred_course_id) {
                    $user->instructorCourses()->syncWithoutDetaching([$application->preferred_course_id]);

                    Course::query()
                        ->whereKey($application->preferred_course_id)
                        ->update(['is_active' => true]);
                }

                if ($application->proposal_type === 'new' && filled($application->proposed_course_name) && filled($application->proposed_course_code)) {
                    $course = Course::query()
                        ->where('title', $application->proposed_course_name)
                        ->where('code', $application->proposed_course_code)
                        ->first();

                    if (! $course) {
                        $course = Course::query()->create([
                            'title' => (string) $application->proposed_course_name,
                            'code' => (string) $application->proposed_course_code,
                            'description' => $application->proposed_course_description,
                            'overview' => $application->proposed_course_overview,
                            'timeline' => $application->proposed_course_timeline,
                            'fees' => self::normalizeFeeProposal((string) ($application->proposed_course_fees ?? '')),
                            'requirements' => self::normalizeLines((string) ($application->proposed_course_requirements ?? '')),
                            'level_progression' => self::normalizeLevelProgressionProposal((string) ($application->proposed_course_level_progression ?? '')),
                            'key_outcome' => $application->proposed_course_key_outcome,
                            'is_open_enrollment' => (bool) $application->proposed_course_is_open_enrollment,
                            'is_active' => true,
                        ]);
                    } else {
                        $course->update([
                            'is_active' => true,
                        ]);
                    }

                    $user->instructorCourses()->syncWithoutDetaching([$course->id]);
                }
            }

            $application->update([
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            Notification::make()
                ->title('Instructor approved')
                ->body("Instructor account for {$application->name} has been activated and course visibility has been updated.")
                ->success()
                ->send();
        } elseif ($application->status === 'rejected') {
            $application->update([
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);

            // Deactivate the user if they have no other approved applications
            $user = $application->user;
            if ($user) {
                $hasOtherApproved = InstructorApplication::query()
                    ->where('user_id', $user->id)
                    ->where('id', '!=', $application->id)
                    ->where('status', 'approved')
                    ->exists();

                if (! $hasOtherApproved) {
                    $user->update(['is_active' => false]);
                }
            }

            Notification::make()
                ->title('Application rejected')
                ->body("Application from {$application->name} has been rejected.")
                ->warning()
                ->send();
        }
    }

    private static function normalizeLines(string $input): ?string
    {
        $lines = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R+/', $input) ?: []
        )));

        if ($lines === []) {
            return null;
        }

        return implode(PHP_EOL, $lines);
    }

    private static function normalizeFeeProposal(string $input): ?string
    {
        $lines = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R+/', $input) ?: []
        )));

        if ($lines === []) {
            return null;
        }

        $entries = [];

        foreach ($lines as $line) {
            $parts = array_values(array_filter(array_map('trim', explode('+', $line))));

            if (count($parts) < 3) {
                continue;
            }

            $categoryRaw = Str::lower($parts[0]);
            $entries[] = [
                'category' => Str::contains($categoryRaw, 'group') ? 'group' : 'one_on_one',
                'level' => self::normalizeLevelLabel($parts[1]),
                'amount' => $parts[2],
                'duration' => $parts[3] ?? '',
            ];
        }

        if ($entries === []) {
            return null;
        }

        $grouped = [
            'one_on_one' => [],
            'group' => [],
        ];

        foreach ($entries as $entry) {
            $grouped[$entry['category']][] = [
                'level' => $entry['level'],
                'amount' => $entry['amount'],
                'duration' => $entry['duration'],
            ];
        }

        return json_encode($grouped, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: null;
    }

    private static function normalizeLevelProgressionProposal(string $input): ?string
    {
        $lines = array_values(array_filter(array_map(
            static fn (string $line): string => trim($line),
            preg_split('/\R+/', $input) ?: []
        )));

        if ($lines === []) {
            return null;
        }

        $entries = [];

        foreach ($lines as $line) {
            $parts = array_values(array_filter(array_map('trim', explode('+', $line))));

            if (count($parts) < 2) {
                continue;
            }

            $entries[] = [
                'level' => self::normalizeLevelLabel($parts[0]),
                'details' => $parts[1],
            ];
        }

        if ($entries === []) {
            return null;
        }

        return json_encode($entries, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: null;
    }

    private static function normalizeLevelLabel(string $value): string
    {
        return match (Str::lower(trim($value))) {
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced',
            default => trim($value),
        };
    }
}
