<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\InstructorApplication;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
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
            'cv' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
            'proposal_type' => ['required', 'in:new,existing'],

            // Existing course fields
            'preferred_course_id' => ['required_if:proposal_type,existing', 'nullable', 'exists:courses,id'],
            'motivation_note' => ['required_if:proposal_type,existing', 'nullable', 'string', 'max:5000'],
            'competence_note' => ['required_if:proposal_type,existing', 'nullable', 'string', 'max:5000'],
            'roadmap' => ['required_if:proposal_type,existing', 'nullable', 'file', 'mimes:pdf', 'max:5120'],

            // New course fields
            'proposed_course_name' => ['required_if:proposal_type,new', 'nullable', 'string', 'max:255'],
            'teaching_location' => ['required_if:proposal_type,new', 'nullable', 'string', 'max:255'],
            'full_roadmap' => ['required_if:proposal_type,new', 'nullable', 'file', 'mimes:pdf', 'max:10240'],
            'curriculum' => ['required_if:proposal_type,new', 'nullable', 'file', 'mimes:pdf', 'max:10240'],
        ]);

        // Enforce max 2 pending/approved applications per email
        $applicationCount = InstructorApplication::query()
            ->where('email', $validated['email'])
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($applicationCount >= 2) {
            return back()->with('warning', 'You can apply for a maximum of 2 courses. You already have ' . $applicationCount . ' active application(s).');
        }

        // Check for duplicate pending application for the same course
        if ($validated['proposal_type'] === 'existing' && $validated['preferred_course_id']) {
            $duplicateCourse = InstructorApplication::query()
                ->where('email', $validated['email'])
                ->where('preferred_course_id', $validated['preferred_course_id'])
                ->where('status', 'pending')
                ->exists();

            if ($duplicateCourse) {
                return back()->with('warning', 'You already have a pending application for this course.');
            }
        }

        $cvPath = null;
        if ($request->hasFile('cv')) {
            $cvPath = $request->file('cv')->store('instructor-cvs', 'public');
        }

        $roadmapPath = null;
        if ($request->hasFile('roadmap')) {
            $roadmapPath = $request->file('roadmap')->store('instructor-roadmaps', 'public');
        }

        $fullRoadmapPath = null;
        if ($request->hasFile('full_roadmap')) {
            $fullRoadmapPath = $request->file('full_roadmap')->store('instructor-roadmaps', 'public');
        }

        $curriculumPath = null;
        if ($request->hasFile('curriculum')) {
            $curriculumPath = $request->file('curriculum')->store('instructor-curricula', 'public');
        }

        // Create or find the instructor user account (inactive until approved)
        $user = User::query()->where('email', $validated['email'])->first();

        if (! $user) {
            $user = User::query()->create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make(Str::random(16)),
                'role' => 'instructor',
                'is_active' => false,
            ]);
        }

        // Remove file upload objects before mass assignment
        unset($validated['cv'], $validated['roadmap'], $validated['full_roadmap'], $validated['curriculum']);

        InstructorApplication::query()->create([
            ...$validated,
            'user_id' => $user->id,
            'cv_path' => $cvPath,
            'roadmap_path' => $roadmapPath,
            'full_roadmap_path' => $fullRoadmapPath,
            'curriculum_path' => $curriculumPath,
        ]);

        return back()->with('success', 'Your instructor application has been submitted successfully! An account has been created for you and will be activated once your application is approved.');
    }
}
