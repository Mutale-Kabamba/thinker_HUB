<?php

use App\Http\Controllers\ProfileController;
use App\Models\Course;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;

$loadPublicCourses = static function (int $limit = 0) {
    try {
        if (config('database.default') === 'sqlite') {
            $sqlitePath = (string) config('database.connections.sqlite.database');

            if (! $sqlitePath || ! is_file($sqlitePath)) {
                return collect();
            }
        }

        if (! Schema::hasTable('courses')) {
            return collect();
        }

        $query = Course::query()
            ->where('is_active', true)
            ->withCount('enrollments')
            ->latest();

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    } catch (\Throwable $e) {
        // If database is unavailable during bootstrap/deploy, avoid hard-failing public pages.
        report($e);

        return collect();
    }
};

$loadHomeStats = static function () {
    $default = [
        'tutors' => 0,
        'students' => 0,
        'courses' => 0,
    ];

    try {
        if (config('database.default') === 'sqlite') {
            $sqlitePath = (string) config('database.connections.sqlite.database');

            if (! $sqlitePath || ! is_file($sqlitePath)) {
                return $default;
            }
        }

        if (Schema::hasTable('users')) {
            $default['tutors'] = User::query()->where('role', 'admin')->count();

            $default['students'] = User::query()
                ->where(function ($query) {
                    $query->whereNull('role')->orWhere('role', '!=', 'admin');
                })
                ->count();
        }

        if (Schema::hasTable('courses')) {
            $default['courses'] = Course::query()->where('is_active', true)->count();
        }

        return $default;
    } catch (\Throwable $e) {
        report($e);

        return $default;
    }
};

Route::get('/', function () use ($loadPublicCourses, $loadHomeStats) {
    $courses = $loadPublicCourses(6);
    $stats = $loadHomeStats();

    return view('welcome', [
        'courses' => $courses,
        'stats' => $stats,
    ]);
})->name('home');

Route::get('/courses', function () use ($loadPublicCourses) {
    $courses = $loadPublicCourses();

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
