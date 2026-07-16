<?php

namespace App\Observers;

use App\Mail\StudentApprovedMail;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    public function updated(User $user): void
    {
        if ($user->role !== 'student') {
            return;
        }

        if (! $user->wasChanged('is_active')) {
            return;
        }

        $wasActive = (bool) $user->getOriginal('is_active');
        $isActive = (bool) $user->is_active;

        if ($wasActive || ! $isActive) {
            return;
        }

        $course = $user->courses()
            ->orderBy('courses.title')
            ->first(['courses.id', 'courses.code', 'courses.title']);

        try {
            Mail::to($user->email)->send(new StudentApprovedMail($user, $course));
        } catch (\Throwable $exception) {
            Log::error('Failed to send student approval confirmation email.', [
                'student_id' => $user->id,
                'student_email' => $user->email,
                'error' => $exception->getMessage(),
            ]);
        }
    }
}
