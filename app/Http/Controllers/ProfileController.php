<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Course;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request, ?User $user = null): View
    {
        $targetUser = $user ?? $request->user();
        $this->authorize('view', $targetUser);

        return view('profile.edit', [
            'user' => $targetUser,
            'isOwnProfile' => (int) $request->user()->id === (int) $targetUser->id,
            'availableCourses' => Course::query()->orderBy('title')->get(['id', 'title', 'code']),
            'enrolledCourseIds' => $targetUser->courses()->pluck('courses.id')->all(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request, ?User $user = null): RedirectResponse
    {
        $targetUser = $user ?? $request->user();
        $this->authorize('update', $targetUser);

        if ($request->hasFile('profile_photo')) {
            if ($targetUser->profile_photo_path) {
                Storage::disk('public')->delete($targetUser->profile_photo_path);
            }

            $targetUser->profile_photo_path = $request->file('profile_photo')->store('profile-photos', 'public');
        }

        $targetUser->fill($request->validated());

        if ($targetUser->isDirty('email')) {
            $targetUser->email_verified_at = null;
        }

        $targetUser->save();

        if ($user) {
            return Redirect::route('filament.admin.resources.users.edit', ['record' => $targetUser])
                ->with('status', 'profile-updated');
        }

        return $targetUser->isAdmin()
            ? Redirect::route('filament.admin.pages.settings')->with('status', 'profile-updated')
            : Redirect::route('filament.student.pages.settings')->with('status', 'profile-updated');
    }

    /**
     * Sync enrollments for a profile owner or admin.
     */
    public function syncEnrollments(Request $request, User $user): RedirectResponse
    {
        $this->authorize('update', $user);

        $data = $request->validate([
            'course_ids' => ['nullable', 'array'],
            'course_ids.*' => ['integer', 'exists:courses,id'],
        ]);

        $user->courses()->sync($data['course_ids'] ?? []);

        return Redirect::route('filament.admin.resources.users.edit', ['record' => $user])
            ->with('status', 'enrollments-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
