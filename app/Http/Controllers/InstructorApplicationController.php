<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\InstructorApplication;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class InstructorApplicationController extends Controller
{
    public function create(): View
    {
        $courses = collect();

        try {
            if (Schema::hasTable('courses')) {
                $courses = Course::query()
                    ->where('is_active', true)
                    ->orderBy('title')
                    ->pluck('title', 'id');
            }
        } catch (\Throwable $e) {
            report($e);
        }

        return view('pages.instructor-apply', [
            'courses' => $courses,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'qualifications' => ['required', 'string', 'max:5000'],
            'experience' => ['nullable', 'string', 'max:5000'],
            'linkedin_url' => ['nullable', 'url', 'max:500'],
            'portfolio_url' => ['nullable', 'url', 'max:500'],
            'course_concept_note' => ['required', 'string', 'max:10000'],
            'proposed_curriculum' => ['required', 'string', 'max:10000'],
            'preferred_course_id' => ['nullable', 'exists:courses,id'],
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('instructor-cvs', 'public');
        }

        // Check for existing pending application
        $existing = InstructorApplication::query()
            ->where('email', $validated['email'])
            ->where('status', 'pending')
            ->first();

        if ($existing) {
            return back()->with('warning', 'You already have a pending application. Please wait for review.');
        }

        InstructorApplication::query()->create([
            ...$validated,
            'cv_path' => $cvPath,
        ]);

        return back()->with('success', 'Your instructor application has been submitted successfully! We will review it and get back to you.');
    }
}
