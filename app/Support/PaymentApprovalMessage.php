<?php

namespace App\Support;

use App\Models\Course;
use App\Models\User;

class PaymentApprovalMessage
{
    public static function forCourse(?Course $course): string
    {
        $adminContact = static::resolveAdminContact();

        return 'Online Payment Coming Soon. For this paid course, contact '.$adminContact.', or the registration team will reach out soon.';
    }

    public static function forUser(User $user): string
    {
        $course = $user->courses()
            ->with(['instructors:id,name,email,whatsapp'])
            ->orderBy('courses.title')
            ->first();

        return static::forCourse($course);
    }

    private static function resolveAdminContact(): string
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

        $name = $admin instanceof User
            ? trim((string) $admin->name)
            : '';
        $phone = $admin instanceof User
            ? trim((string) $admin->whatsapp)
            : '';
        $email = $admin instanceof User
            ? trim((string) $admin->email)
            : '';

        if ($name === '') {
            $name = '<Admin Name>';
        }

        if ($phone === '') {
            $phone = '<Admin Phone>';
        }

        if ($email === '') {
            $email = '<Admin Email>';
        }

        return $value = $name.' on '.$phone.' | '.$email;
    }
}
