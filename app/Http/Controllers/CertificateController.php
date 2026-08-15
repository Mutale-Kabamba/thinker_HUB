<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use App\Models\Enrollment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    /**
     * Print-optimized certificate page (use the browser's "Save as PDF").
     * Only the certificate owner (with instructor sign-off) or an admin may view it.
     */
    public function download(Request $request, Certificate $certificate): View
    {
        $user = $request->user();

        abort_unless($user, 401);

        $enrollment = Enrollment::query()
            ->where('user_id', $certificate->user_id)
            ->where('course_id', $certificate->course_id)
            ->first();

        $isCompleted = (bool) ($enrollment && $enrollment->completed_at !== null);

        abort_unless(
            $user->role === 'admin' || ($user->id === $certificate->user_id && $isCompleted),
            403,
            'This certificate is locked until the course completion is signed off by your instructor.'
        );

        $certificate->loadMissing(['user', 'course.instructors']);

        return view('certificates.certificate', [
            'certificate' => $certificate,
        ]);
    }

    /**
     * Public authenticity check for a verification code.
     * Only valid if the student enrollment has been signed off by the instructor.
     */
    public function verify(string $code): View
    {
        $certificate = Certificate::query()
            ->with(['user', 'course'])
            ->where('verification_code', $code)
            ->first();

        $enrollment = $certificate ? Enrollment::query()
            ->where('user_id', $certificate->user_id)
            ->where('course_id', $certificate->course_id)
            ->first() : null;

        $isValid = $certificate && $enrollment && $enrollment->completed_at !== null;

        return view('certificates.verify', [
            'certificate' => $isValid ? $certificate : null,
            'code' => $code,
        ]);
    }
}
