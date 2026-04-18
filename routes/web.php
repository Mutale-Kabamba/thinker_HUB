<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InstructorApplicationController;
use App\Models\Course;
use App\Models\CourseRating;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

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
            ->withAvg('ratings', 'rating')
            ->withCount('ratings')
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

            $default['students'] = User::query()->where('role', 'student')->count();
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

$courseSlug = static function (Course $course): string {
    $source = trim((string) ($course->title ?: $course->code ?: $course->id));

    return Str::slug($source);
};

$databaseReady = static function (): bool {
    if (config('database.default') === 'sqlite') {
        $sqlitePath = (string) config('database.connections.sqlite.database');

        if (! $sqlitePath || ! is_file($sqlitePath)) {
            return false;
        }
    }

    return true;
};

Route::get('/', function () use ($loadPublicCourses, $loadHomeStats) {
    $allCourses = $loadPublicCourses();
    $coursesWithStudents = $allCourses
        ->filter(fn (Course $course): bool => (int) ($course->enrollments_count ?? 0) > 0)
        ->sortByDesc(fn (Course $course): int => (int) ($course->enrollments_count ?? 0))
        ->values();

    $coursesWithoutStudents = $allCourses
        ->filter(fn (Course $course): bool => (int) ($course->enrollments_count ?? 0) === 0)
        ->shuffle()
        ->values();

    $courses = $coursesWithStudents->take(3);

    if ($courses->count() < 3) {
        $courses = $courses
            ->concat($coursesWithoutStudents->take(3 - $courses->count()))
            ->values();
    }

    $courses = $courses->take(3);
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

Route::get('/courses/{course}/{slug?}', function (int $course, ?string $slug = null) use ($courseSlug, $databaseReady) {
    if (! $databaseReady() || ! Schema::hasTable('courses')) {
        abort(404);
    }

    $courseModel = Course::query()->where('is_active', true)->findOrFail($course);
    $courseModel->loadAvg('ratings', 'rating');
    $courseModel->loadCount('ratings');
    $courseModel->load(['ratings' => function ($q) {
        $q->with('user:id,name,profile_photo_path')->latest()->limit(10);
    }]);
    $canonicalSlug = $courseSlug($courseModel);
    $relatedCourses = Course::query()
        ->where('is_active', true)
        ->whereKeyNot($courseModel->id)
        ->latest()
        ->limit(3)
        ->get(['id', 'title', 'code', 'overview'])
        ->each(function (Course $item) use ($courseSlug) {
            $item->setAttribute('seo_slug', $courseSlug($item));
        });

    if ($slug !== null && $slug !== $canonicalSlug) {
        return redirect()->route('landing.courses.show', ['course' => $courseModel->id, 'slug' => $canonicalSlug], 301);
    }

    return view('pages.course', [
        'course' => $courseModel,
        'slug' => $canonicalSlug,
        'relatedCourses' => $relatedCourses,
    ]);
})->whereNumber('course')->name('landing.courses.show');

Route::post('/courses/{course}/rate', function (Request $request, int $course) use ($databaseReady) {
    if (! $databaseReady()) {
        abort(404);
    }

    $courseModel = Course::query()->where('is_active', true)->findOrFail($course);
    $user = auth()->user();

    abort_unless($user, 403);
    abort_unless($user->courses()->where('courses.id', $courseModel->id)->exists(), 403, 'You must be enrolled to rate this course.');

    $validated = $request->validate([
        'rating' => 'required|integer|min:1|max:5',
        'review' => 'nullable|string|max:1000',
    ]);

    CourseRating::updateOrCreate(
        ['course_id' => $courseModel->id, 'user_id' => $user->id],
        ['rating' => $validated['rating'], 'review' => $validated['review']],
    );

    return redirect()->back()->with('success', 'Your review has been saved!');
})->middleware('auth')->name('course.rate');

Route::get('/instructors', function () {
    $instructors = collect();
    try {
        if (Schema::hasTable('users')) {
            $instructors = User::query()
                ->where('role', 'instructor')
                ->get(['id', 'name', 'profile_photo_path', 'proficiency', 'occupation', 'whatsapp', 'linkedin_url', 'facebook_url']);
        }
    } catch (\Throwable $e) {
        report($e);
    }

    return view('pages.instructors', ['instructors' => $instructors]);
})->name('landing.instructors');

Route::get('/instructors/apply', [InstructorApplicationController::class, 'create'])->name('landing.instructors.apply');
Route::post('/instructors/apply', [InstructorApplicationController::class, 'store'])->name('landing.instructors.apply.store');

Route::view('/contact', 'pages.contact')->name('landing.contact');

Route::get('/sitemap.xml', function () use ($databaseReady, $courseSlug) {
    $pages = [
        ['loc' => route('home'), 'lastmod' => now()->toDateString(), 'changefreq' => 'weekly', 'priority' => '1.0'],
        ['loc' => route('landing.courses'), 'lastmod' => now()->toDateString(), 'changefreq' => 'weekly', 'priority' => '0.9'],
        ['loc' => route('landing.instructors'), 'lastmod' => now()->toDateString(), 'changefreq' => 'monthly', 'priority' => '0.7'],
        ['loc' => route('landing.contact'), 'lastmod' => now()->toDateString(), 'changefreq' => 'monthly', 'priority' => '0.6'],
    ];

    try {
        if ($databaseReady() && Schema::hasTable('courses')) {
            $courses = Course::query()->where('is_active', true)->latest('updated_at')->get(['id', 'title', 'code', 'updated_at']);

            foreach ($courses as $course) {
                $pages[] = [
                    'loc' => route('landing.courses.show', ['course' => $course->id, 'slug' => $courseSlug($course)]),
                    'lastmod' => optional($course->updated_at)->toDateString() ?: now()->toDateString(),
                    'changefreq' => 'weekly',
                    'priority' => '0.8',
                ];
            }
        }
    } catch (\Throwable $e) {
        report($e);
    }

    return response()
        ->view('sitemap', ['pages' => $pages])
        ->header('Content-Type', 'application/xml; charset=UTF-8')
        ->header('Cache-Control', 'public, max-age=3600');
})->withoutMiddleware([
    \Illuminate\Cookie\Middleware\EncryptCookies::class,
    \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
    \Illuminate\Session\Middleware\StartSession::class,
    \Illuminate\View\Middleware\ShareErrorsFromSession::class,
    \Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class,
])->name('sitemap');

Route::redirect('/enroll', '/register')->name('enroll');
Route::redirect('/become-student', '/register')->name('become-student');

Route::get('/dashboard', function () {
    $adminEmail = strtolower((string) env('ADMIN_EMAIL', 'admin@example.com'));
    $user = Auth::user();
    $email = strtolower((string) $user?->email);
    $isAdmin = $user?->role === 'admin' || $email === $adminEmail;

    if ($isAdmin) {
        return redirect()->route('filament.admin.pages.dashboard');
    }

    if ($user?->role === 'instructor') {
        return redirect('/teach/instructor-overview');
    }

    return redirect()->route('filament.student.pages.overview');
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

    // Serve files from storage without requiring the storage:link symlink.
    Route::get('/file/view/{type}/{id}', function (string $type, int $id) {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        if ($type === 'material') {
            $material = \App\Models\LearningMaterial::query()->visibleTo($user)->findOrFail($id);
            $path = $material->file_path;
        } elseif ($type === 'assignment') {
            $assignment = \App\Models\Assignment::query()->visibleTo($user)->findOrFail($id);
            $path = $assignment->file_path;
        } elseif ($type === 'assessment') {
            if ($user->isAdmin()) {
                $assessment = \App\Models\Assessment::query()->findOrFail($id);
            } else {
                $assessment = \App\Models\Assessment::query()->where('user_id', $user->id)->findOrFail($id);
            }
            $path = $assessment->file_path;
        } elseif ($type === 'submission') {
            $submission = \App\Models\AssignmentSubmission::query()
                ->where('user_id', $user->id)
                ->findOrFail($id);
            $path = $submission->file_path;
        } elseif ($type === 'assessment-submission') {
            $submission = \App\Models\AssessmentSubmission::query()
                ->where('user_id', $user->id)
                ->findOrFail($id);
            $path = $submission->file_path;
        } else {
            abort(404);
        }

        if (! $path || ! $disk->exists($path)) {
            return response(
                '<html><body style="margin:0;display:flex;align-items:center;justify-content:center;height:100vh;font-family:system-ui,sans-serif;color:#6b7280;background:#f9fafb;">'
                .'<div style="text-align:center;padding:2rem;"><svg xmlns="http://www.w3.org/2000/svg" style="width:48px;height:48px;margin:0 auto 1rem;color:#d1d5db;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>'
                .'<p style="margin:0 0 0.5rem;font-size:1rem;font-weight:600;">File not found</p>'
                .'<p style="margin:0;font-size:0.85rem;">The file may have been removed or is not yet available.</p></div></body></html>',
                404,
                ['Content-Type' => 'text/html']
            );
        }

        return $disk->response($path);
    })->name('file.view');

    // Signed URL route for viewing Office documents via Google Docs Viewer.
    // Generates a temporary signed URL that doesn't require authentication.
    Route::get('/file/signed/{type}/{id}', function (string $type, int $id) {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $disk = \Illuminate\Support\Facades\Storage::disk('public');

        if ($type === 'material') {
            $material = \App\Models\LearningMaterial::query()->visibleTo($user)->findOrFail($id);
            $path = $material->file_path;
        } elseif ($type === 'assignment') {
            $assignment = \App\Models\Assignment::query()->visibleTo($user)->findOrFail($id);
            $path = $assignment->file_path;
        } elseif ($type === 'assessment') {
            if ($user->isAdmin()) {
                $assessment = \App\Models\Assessment::query()->findOrFail($id);
            } else {
                $assessment = \App\Models\Assessment::query()->where('user_id', $user->id)->findOrFail($id);
            }
            $path = $assessment->file_path;
        } elseif ($type === 'submission') {
            $submission = \App\Models\AssignmentSubmission::query()
                ->where('user_id', $user->id)
                ->findOrFail($id);
            $path = $submission->file_path;
        } elseif ($type === 'assessment-submission') {
            $submission = \App\Models\AssessmentSubmission::query()
                ->where('user_id', $user->id)
                ->findOrFail($id);
            $path = $submission->file_path;
        } else {
            abort(404);
        }

        if (! $path || ! $disk->exists($path)) {
            abort(404);
        }

        $signedUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'file.public',
            now()->addMinutes(30),
            ['path' => $path]
        );

        return response()->json(['url' => $signedUrl]);
    })->name('file.signed');
});

// Publicly accessible signed route for Google Docs Viewer to fetch the file.
Route::get('/file/public', function (\Illuminate\Http\Request $request) {
    if (! $request->hasValidSignature()) {
        abort(403);
    }

    $path = $request->query('path');
    $disk = \Illuminate\Support\Facades\Storage::disk('public');

    // Prevent path traversal attacks.
    if (! $path || str_contains($path, '..') || ! $disk->exists($path)) {
        abort(404);
    }

    return $disk->response($path);
})->name('file.public');

require __DIR__.'/auth.php';

Route::domain('www.thinker.it.com')->group(function () {
    Route::get('/{path?}', function (Request $request, ?string $path = '') {
        $target = 'https://thinker.it.com/' . ltrim((string) $path, '/');
        $query = $request->getQueryString();

        if ($query) {
            $target .= '?' . $query;
        }

        return redirect()->to($target, 301);
    })->where('path', '.*');
});
