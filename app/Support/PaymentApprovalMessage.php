<?php

namespace App\Support;

use App\Models\Course;
use App\Models\User;

class PaymentApprovalMessage
{
    public static function forCourse(?Course $course): string
    {
        $instructorContact = static::resolveInstructorContact($course);
        $adminContact = static::resolveAdminContact();

        if ($instructorContact && $adminContact) {
            return 'Online Payment Coming Soon. For this paid course, contact '.$instructorContact.' or '.$adminContact.', or the registration team will reach out soon.';
        }

        if ($instructorContact) {
            return 'Online Payment Coming Soon. For this paid course, contact '.$instructorContact.', or the registration team will reach out soon.';
        }

        if ($adminContact) {
            return 'Online Payment Coming Soon. For this paid course, contact '.$adminContact.', or the registration team will reach out soon.';
        }

        return 'Online Payment Coming Soon. For this paid course, the registration team will reach out soon.';
    }

    public static function forUser(User $user): string
    {
        $course = $user->courses()
            ->with(['instructors:id,name,email,whatsapp'])
            ->orderBy('courses.title')
            ->first();

        return static::forCourse($course);
    }

    private static function resolveInstructorContact(?Course $course): ?string
    {
        if (! $course) {
            return null;
        }

        if ($course->relationLoaded('instructors')) {
            $instructor = $course->instructors->first();
        } else {
            $instructor = $course->instructors()->orderBy('name')->first(['users.name', 'users.email', 'users.whatsapp']);
        }

        if (! $instructor instanceof User) {
            return null;
        }

        $contact = trim((string) ($instructor->whatsapp ?: $instructor->email));

        if ($contact === '') {
            return null;
        }

        return trim($instructor->name).' (Instructor of the selected course) on '.$contact;
    }

    private static function resolveAdminContact(): ?string
    {
        static $cached = false;
        static $value = null;

        if ($cached) {
            return $value;
        }

        $cached = true;

        $admin = User::query()
            ->where('role', 'admin')
            ->where('is_active', true)
            ->orderBy('name')
            ->first(['name', 'email', 'whatsapp']);

        if (! $admin instanceof User) {
            return $value = null;
        }

        $contact = trim((string) ($admin->whatsapp ?: $admin->email));

        if ($contact === '') {
            return $value = null;
        }

        return $value = trim($admin->name).' (Admin) on '.$contact;
    }
}
