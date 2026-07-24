<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CertificateController extends Controller
{
    /**
     * Print-optimized certificate page (use the browser's "Save as PDF").
     * Only the certificate owner (or an admin) may view it.
     */
    public function download(Request $request, Certificate $certificate): View
    {
        $user = $request->user();

        abort_unless(
            $user && ($user->id === $certificate->user_id || $user->role === 'admin'),
            403,
        );

        $certificate->loadMissing(['user', 'course.instructors']);

        return view('certificates.certificate', [
            'certificate' => $certificate,
        ]);
    }

    /**
     * Public authenticity check for a verification code.
     */
    public function verify(string $code): View
    {
        $certificate = Certificate::query()
            ->with(['user', 'course'])
            ->where('verification_code', $code)
            ->first();

        return view('certificates.verify', [
            'certificate' => $certificate,
            'code' => $code,
        ]);
    }
}
