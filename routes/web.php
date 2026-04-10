<?php

use App\Http\Controllers\ProfileController;
use App\Models\Course;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

Route::get('/', function () {
    $courses = Schema::hasTable('courses')
        ? Course::query()
            ->where('is_active', true)
            ->withCount('enrollments')
            ->latest()
            ->limit(6)
            ->get()
        : collect();

    return view('welcome', [
        'courses' => $courses,
    ]);
})->name('home');

Route::get('/courses', function () {
    $courses = Schema::hasTable('courses')
        ? Course::query()
            ->where('is_active', true)
            ->withCount('enrollments')
            ->latest()
            ->get()
        : collect();

    return view('pages.courses', [
        'courses' => $courses,
    ]);
})->name('landing.courses');

Route::view('/instructors', 'pages.instructors')->name('landing.instructors');

Route::view('/contact', 'pages.contact')->name('landing.contact');

Route::redirect('/enroll', '/register')->name('enroll');
Route::redirect('/become-student', '/register')->name('become-student');

Route::get('/dashboard', function () {
    $adminEmail = strtolower((string) env('ADMIN_EMAIL', 'admin@example.com'));
    $user = Auth::user();
    $email = strtolower((string) $user?->email);
    $isAdmin = $user?->role === 'admin' || $email === $adminEmail;

    return $isAdmin
    ? redirect()->route('filament.admin.pages.dashboard')
        : redirect()->route('filament.student.pages.overview');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        return $user->isAdmin()
            ? redirect()->route('filament.admin.pages.settings')
            : redirect()->route('filament.student.pages.settings');
    })->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profiles/{user}', function ($user) {
        return redirect()->route('filament.admin.resources.users.edit', ['record' => $user]);
    })->name('profiles.edit');
    Route::patch('/profiles/{user}', [ProfileController::class, 'update'])->name('profiles.update');
    Route::put('/profiles/{user}/enrollments', [ProfileController::class, 'syncEnrollments'])->name('profiles.enrollments.sync');

    Route::prefix('student')->name('student.')->group(function () {
        Route::redirect('/overview', '/learn/overview')->name('overview');
        Route::redirect('/courses', '/learn/courses')->name('courses');
        Route::redirect('/assignments', '/learn/assignments')->name('assignments');
        Route::redirect('/assessments', '/learn/assessments')->name('assessments');
        Route::redirect('/materials', '/learn/materials')->name('materials');
    });

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::redirect('/overview', '/manage')->name('overview');
        Route::redirect('/students', '/manage/users')->name('students');
        Route::redirect('/courses', '/manage/courses')->name('courses');
        Route::redirect('/assignments', '/manage/assignments')->name('assignments');
        Route::redirect('/assessments', '/manage/assessments')->name('assessments');
        Route::redirect('/materials', '/manage/learning-materials')->name('materials');
    });
});

require __DIR__.'/auth.php';
