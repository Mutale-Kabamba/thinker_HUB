<?php

use App\Http\Controllers\CertificateController;
use App\Http\Controllers\ContactMessageController;
use App\Http\Controllers\InstructorApplicationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Livewire\Reviews\CreateReviewPage;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\Course;
use App\Models\CourseRating;
use App\Models\HubPost;
use App\Models\InstructorApplication;
use App\Models\LearningMaterial;
use App\Models\Media;
use App\Models\Review;
use App\Models\User;
use App\Support\PublicDiskPath;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Illuminate\View\Middleware\ShareErrorsFromSession;

$loadPublicCourses = static function (int $limit = 0) {
    try {
        if (config('database.default') === 'sqlite') {
            $sqlitePath = (string) config('database.connections.sqlite.database');

            if (! $sqlitePath || ($sqlitePath !== ':memory:' && ! is_file($sqlitePath))) {
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

        if (Schema::hasTable('course_instructor') && Schema::hasTable('users')) {
            $query->with([
                'instructors' => static fn ($instructorQuery) => $instructorQuery
                    ->select('users.id', 'name')
                    ->orderBy('name'),
            ]);
        }

        if (Schema::hasTable('course_selected_participants')) {
            $query->withCount('selectedParticipants');
        }

        if (Schema::hasTable('course_intakes')) {
            $query->with(['intakes' => fn ($q) => $q->orderBy('start_date')]);
        }

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query->get();
    } catch (Throwable $e) {
        // If database is unavailable during bootstrap/deploy, avoid hard-failing public pages.
        report($e);

        return collect();
    }
};

$publicCourseStudentCount = static function (Course $course): int {
    $enrolled = (int) ($course->enrollments_count ?? 0);

    if ($enrolled > 0) {
        return $enrolled;
    }

    return (int) ($course->selected_participants_count ?? 0);
};

$loadHomeStats = static function () {
    $default = [
        'tutors' => 0,
        'students' => 0,
        'courses' => 0,
        'avg_rating' => 5.0,
        'rating_count' => 0,
    ];

    try {
        if (config('database.default') === 'sqlite') {
            $sqlitePath = (string) config('database.connections.sqlite.database');

            if (! $sqlitePath || ($sqlitePath !== ':memory:' && ! is_file($sqlitePath))) {
                return $default;
            }
        }

        if (Schema::hasTable('users')) {
            $default['tutors'] = User::query()->where('role', 'instructor')->where('is_active', true)->count();

            $default['students'] = User::query()->where('role', 'student')->count();
        }

        if (Schema::hasTable('courses')) {
            $default['courses'] = Course::query()->where('is_active', true)->count();
        }

        if (Schema::hasTable('reviews')) {
            $platformReviews = Review::query()->platform()->where('is_approved', true)->whereNotNull('rating');
            $platformCount = $platformReviews->count();
            if ($platformCount > 0) {
                $default['avg_rating'] = round((float) $platformReviews->avg('rating'), 1);
                $default['rating_count'] = $platformCount;
            } else {
                $allApproved = Review::query()->where('is_approved', true)->whereNotNull('rating');
                $allCount = $allApproved->count();
                if ($allCount > 0) {
                    $default['avg_rating'] = round((float) $allApproved->avg('rating'), 1);
                    $default['rating_count'] = $allCount;
                } elseif (Schema::hasTable('course_ratings')) {
                    $legacyCount = CourseRating::query()->count();
                    if ($legacyCount > 0) {
                        $default['avg_rating'] = round((float) CourseRating::query()->avg('rating'), 1);
                        $default['rating_count'] = $legacyCount;
                    }
                }
            }
        } elseif (Schema::hasTable('course_ratings')) {
            $legacyCount = CourseRating::query()->count();
            if ($legacyCount > 0) {
                $default['avg_rating'] = round((float) CourseRating::query()->avg('rating'), 1);
                $default['rating_count'] = $legacyCount;
            }
        }

        return $default;
    } catch (Throwable $e) {
        report($e);

        return $default;
    }
};

$loadRecentCourseReviews = static function (int $limit = 6) {
    try {
        if (config('database.default') === 'sqlite') {
            $sqlitePath = (string) config('database.connections.sqlite.database');

            if (! $sqlitePath || ($sqlitePath !== ':memory:' && ! is_file($sqlitePath))) {
                return collect();
            }
        }

        if (Schema::hasTable('reviews') && Schema::hasTable('users')) {
            $reviews = Review::query()
                ->where('is_approved', true)
                ->with(['user:id,name', 'reviewable'])
                ->latest()
                ->limit($limit)
                ->get();

            if ($reviews->isNotEmpty()) {
                return $reviews;
            }
        }

        if (! Schema::hasTable('course_ratings') || ! Schema::hasTable('courses') || ! Schema::hasTable('users')) {
            return collect();
        }

        return CourseRating::query()
            ->whereNotNull('review')
            ->where('review', '!=', '')
            ->with([
                'user:id,name',
                'course:id,title',
            ])
            ->latest()
            ->limit($limit)
            ->get();
    } catch (Throwable $e) {
        report($e);

        return collect();
    }
};

$loadGlobalRatingStats = static function () {
    try {
        if (config('database.default') === 'sqlite') {
            $sqlitePath = (string) config('database.connections.sqlite.database');

            if (! $sqlitePath || ($sqlitePath !== ':memory:' && ! is_file($sqlitePath))) {
                return [
                    'avgRating' => 5.0,
                    'totalRatingsCount' => 0,
                    'starCounts' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
                ];
            }
        }

        if (Schema::hasTable('reviews')) {
            $ratedReviews = Review::query()->where('is_approved', true)->whereNotNull('rating')->get();
            $total = $ratedReviews->count();
            if ($total > 0) {
                return [
                    'avgRating' => round((float) $ratedReviews->avg('rating'), 1),
                    'totalRatingsCount' => $total,
                    'starCounts' => [
                        5 => $ratedReviews->where('rating', 5)->count(),
                        4 => $ratedReviews->where('rating', 4)->count(),
                        3 => $ratedReviews->where('rating', 3)->count(),
                        2 => $ratedReviews->where('rating', 2)->count(),
                        1 => $ratedReviews->where('rating', 1)->count(),
                    ],
                ];
            }
        }

        if (! Schema::hasTable('course_ratings')) {
            return [
                'avgRating' => 5.0,
                'totalRatingsCount' => 0,
                'starCounts' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
            ];
        }

        $allRatings = CourseRating::query()->get();
        $total = $allRatings->count();
        $avg = $total > 0 ? round((float) $allRatings->avg('rating'), 1) : 5.0;

        return [
            'avgRating' => $avg,
            'totalRatingsCount' => $total,
            'starCounts' => [
                5 => $allRatings->where('rating', 5)->count(),
                4 => $allRatings->where('rating', 4)->count(),
                3 => $allRatings->where('rating', 3)->count(),
                2 => $allRatings->where('rating', 2)->count(),
                1 => $allRatings->where('rating', 1)->count(),
            ],
        ];
    } catch (Throwable $e) {
        report($e);

        return [
            'avgRating' => 5.0,
            'totalRatingsCount' => 0,
            'starCounts' => [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0],
        ];
    }
};

$courseSlug = static function (Course $course): string {
    $source = trim((string) ($course->title ?: $course->code ?: $course->id));

    return Str::slug($source);
};

$instructorSlug = static function (User $instructor): string {
    $source = trim((string) ($instructor->name ?: $instructor->id));

    return Str::slug($source);
};

$databaseReady = static function (): bool {
    if (config('database.default') === 'sqlite') {
        $sqlitePath = (string) config('database.connections.sqlite.database');

        if ($sqlitePath === ':memory:') {
            return true;
        }

        if (! $sqlitePath || ! is_file($sqlitePath)) {
            return false;
        }
    }

    return true;
};

Route::get('/', function () use ($loadPublicCourses, $loadHomeStats, $publicCourseStudentCount, $loadRecentCourseReviews, $loadGlobalRatingStats) {
    $allCourses = $loadPublicCourses();
    $coursesWithStudents = $allCourses
        ->filter(fn (Course $course): bool => $publicCourseStudentCount($course) > 0)
        ->sortByDesc(fn (Course $course): int => $publicCourseStudentCount($course))
        ->values();

    $coursesWithoutStudents = $allCourses
        ->filter(fn (Course $course): bool => $publicCourseStudentCount($course) === 0)
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
    $reviews = $loadRecentCourseReviews();
    $ratingStats = $loadGlobalRatingStats();

    return view('welcome', [
        'courses' => $courses,
        'stats' => $stats,
        'reviews' => $reviews,
        'ratingStats' => $ratingStats,
    ]);
})->name('home');

Route::get('/courses', function () use ($loadPublicCourses, $loadRecentCourseReviews, $loadGlobalRatingStats) {
    $courses = $loadPublicCourses();
    $reviews = $loadRecentCourseReviews();
    $ratingStats = $loadGlobalRatingStats();

    return view('pages.courses', [
        'courses' => $courses,
        'reviews' => $reviews,
        'ratingStats' => $ratingStats,
    ]);
})->name('landing.courses');

Route::get('/hub', App\Livewire\Public\HubIndex::class)->name('hub.index');
Route::get('/hub/{post:slug}', App\Livewire\Public\HubShow::class)->name('hub.show');
Route::get('/media/{media}/download', function (App\Models\Media $media) {
    return \App\Support\DocumentResponse::serve(
        rawPath: $media->path,
        downloadName: $media->original_name,
        isDownload: true,
        preferredDisk: $media->disk
    );
})->name('media.download');

Route::get('/media/{media}/view', function (App\Models\Media $media, Request $request) {
    return \App\Support\DocumentResponse::serve(
        rawPath: $media->path,
        downloadName: $media->original_name,
        isDownload: (bool) $request->query('download'),
        preferredDisk: $media->disk,
        extraHeaders: [
            'X-Frame-Options' => 'SAMEORIGIN',
            'Content-Security-Policy' => "frame-ancestors 'self'",
        ]
    );
})->name('media.view');

Route::get('/checkout/{course}', [PaymentController::class, 'showCheckout'])->name('checkout.show');
Route::get('/courses/{course}/enroll', [PaymentController::class, 'showCheckout']);
Route::post('/checkout/{course}/apply-promo', [PaymentController::class, 'applyPromoCode'])->name('checkout.apply-promo');
Route::post('/courses/{course}/pay', [PaymentController::class, 'processPayment'])->name('checkout.process');
Route::get('/payments/receipt/{reference}', [PaymentController::class, 'showReceipt'])->name('payment.receipt');
Route::get('/payments/status/{reference}', [PaymentController::class, 'checkStatus'])->name('payment.status');
Route::post('/api/payments/webhook/lenco', [PaymentController::class, 'handleWebhook'])->name('payment.webhook.lenco');
Route::post('/api/payments/webhook/broadpay', [PaymentController::class, 'handleWebhook'])->name('payment.webhook.broadpay');
Route::post('/payments/webhook', [PaymentController::class, 'handleWebhook'])->name('payment.webhook');

Route::post('/notifications/{id}/read', function (string $id) {
    auth()->user()?->notifications()->where('id', $id)->update(['read_at' => now()]);
    return response()->json(['success' => true]);
})->middleware('auth')->name('notifications.read');

Route::post('/notifications/{id}/clear', function (string $id) {
    auth()->user()?->notifications()->where('id', $id)->delete();
    return response()->json(['success' => true]);
})->middleware('auth')->name('notifications.clear');

Route::get('/courses/{course}/{slug?}', function (int $course, ?string $slug = null) use ($courseSlug, $databaseReady) {
    if (! $databaseReady() || ! Schema::hasTable('courses')) {
        abort(404);
    }

    $courseModel = Course::query()->where('is_active', true)->findOrFail($course);
    $courseModel->loadAvg('ratings', 'rating');
    $courseModel->loadCount('ratings');
    if (Schema::hasTable('course_intakes')) {
        $courseModel->load(['intakes' => fn ($q) => $q->orderBy('start_date')]);
    }
    $courseModel->load(['ratings' => function ($q) {
        $q->with('user:id,name,profile_photo_path')->latest()->limit(10);
    }]);
    $canonicalSlug = $courseSlug($courseModel);
    $relatedCourses = Course::query()
        ->where('is_active', true)
        ->whereKeyNot($courseModel->id)
        ->latest()
        ->limit(3)
        ->get(['id', 'title', 'code', 'overview', 'image_path'])
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

    $review = isset($validated['review']) ? trim((string) $validated['review']) : '';

    CourseRating::updateOrCreate(
        ['course_id' => $courseModel->id, 'user_id' => $user->id],
        ['rating' => $validated['rating'], 'review' => $review !== '' ? $review : null],
    );

    \App\Models\Review::updateOrCreate(
        [
            'user_id' => $user->id,
            'reviewable_type' => Course::class,
            'reviewable_id' => $courseModel->id,
        ],
        [
            'rating' => $validated['rating'],
            'title' => null,
            'comment' => $review !== '' ? $review : 'Course rating',
            'is_verified' => true,
            'is_approved' => true,
        ]
    );

    return redirect()->back()->with('success', 'Your review has been saved!');
})->middleware('auth')->name('course.rate');

Route::get('/network', function (Request $request) {
    $members = collect();
    $activeRole = (string) $request->query('role', 'all');

    try {
        if (Schema::hasTable('users')) {
            $query = User::query()
                ->where(function ($q) {
                    $q->whereIn('role', ['instructor', 'blogger', 'researcher', 'employer'])
                        ->orWhere(fn ($sub) => $sub->where('role', 'admin')->whereHas('instructorCourses'));
                })
                ->where('is_active', true);

            if ($activeRole !== 'all' && in_array($activeRole, ['instructor', 'blogger', 'researcher', 'employer'], true)) {
                if ($activeRole === 'instructor') {
                    $query->where(fn ($q) => $q->where('role', 'instructor')->orWhere(fn ($sub) => $sub->where('role', 'admin')->whereHas('instructorCourses')));
                } else {
                    $query->where('role', $activeRole);
                }
            }

            $members = $query->get([
                'id', 'name', 'email', 'role', 'profile_photo_path', 'proficiency', 'occupation',
                'company', 'portfolio_url', 'github_url', 'instagram_url', 'specialty', 'whatsapp', 'linkedin_url', 'facebook_url', 'bio'
            ]);
        }
    } catch (Throwable $e) {
        report($e);
    }

    return view('pages.instructors', [
        'instructors' => $members,
        'activeRole' => $activeRole,
    ]);
})->name('landing.instructors');

Route::get('/instructors', fn (Request $request) => redirect()->route('landing.instructors', $request->query()));

Route::get('/network/{instructor}/{slug?}', function (int $instructor, ?string $slug = null) use ($databaseReady, $instructorSlug) {
    if (! $databaseReady() || ! Schema::hasTable('users')) {
        abort(404);
    }

    $with = [
        'instructorCourses' => function ($query): void {
            $query
                ->where('is_active', true)
                ->withCount('enrollments')
                ->withAvg('ratings', 'rating')
                ->withCount('ratings')
                ->latest();
        },
    ];

    if (Schema::hasTable('instructor_applications')) {
        $with[] = 'instructorApplication';
    }

    $memberModel = User::query()
        ->whereIn('role', ['instructor', 'blogger', 'researcher', 'employer'])
        ->where('is_active', true)
        ->with($with)
        ->findOrFail($instructor);

    $canonicalSlug = $instructorSlug($memberModel);

    if ($slug !== null && $slug !== $canonicalSlug) {
        return redirect()->route('landing.instructors.show', ['instructor' => $memberModel->id, 'slug' => $canonicalSlug], 301);
    }

    $posts = HubPost::query()
        ->where('author_id', $memberModel->id)
        ->published()
        ->latest()
        ->get();

    return view('pages.instructor', [
        'instructor' => $memberModel,
        'slug' => $canonicalSlug,
        'courses' => $memberModel->instructorCourses ?? collect(),
        'posts' => $posts,
    ]);
})->whereNumber('instructor')->name('landing.instructors.show');

Route::get('/instructors/apply', [InstructorApplicationController::class, 'create'])->name('landing.instructors.apply');
Route::post('/instructors/apply', [InstructorApplicationController::class, 'store'])->name('landing.instructors.apply.store');

Route::get('/reviews/create', function (\Illuminate\Http\Request $request) {
    if (! Auth::check()) {
        return redirect()->guest(route('login'));
    }

    return redirect()->route('filament.student.pages.reviews', $request->query());
})->name('reviews.create');
Route::redirect('/reviews/write', '/reviews/create');

Route::view('/contact', 'pages.contact')->name('landing.contact');
Route::post('/contact', [ContactMessageController::class, 'store'])->middleware('throttle:3,1')->name('landing.contact.store');
Route::view('/privacy', 'pages.privacy')->name('landing.privacy');
Route::view('/cookies', 'pages.cookies')->name('landing.cookies');
Route::view('/terms', 'pages.terms')->name('landing.terms');

Route::get('/sitemap.xml', function () {
    $sitemapPath = public_path('sitemap.xml');

    if (! is_file($sitemapPath)) {
        Artisan::call('seo:generate');
    }

    abort_unless(is_file($sitemapPath), 404);

    return response()->file($sitemapPath, [
        'Content-Type' => 'application/xml; charset=UTF-8',
        'Cache-Control' => 'public, max-age=3600',
    ]);
})->withoutMiddleware([
    EncryptCookies::class,
    AddQueuedCookiesToResponse::class,
    StartSession::class,
    ShareErrorsFromSession::class,
    VerifyCsrfToken::class,
])->name('sitemap');

Route::redirect('/enroll', '/register')->name('enroll');
Route::redirect('/become-student', '/register')->name('become-student');
Route::redirect('/parents', '/parent');
Route::redirect('/parent-portal', '/parent');
Route::redirect('/parents-portal', '/parent');

Route::get('/dashboard', function () {
    $user = Auth::user();

    if (! $user) {
        return redirect()->route('login');
    }

    return redirect($user->default_portal_url);
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/certificates/verify/{code}', [CertificateController::class, 'verify'])->name('certificates.verify');

Route::middleware('auth')->group(function () {
    Route::get('/certificates/{certificate}/download', [CertificateController::class, 'download'])->name('certificates.download');

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

    Route::post('/teach/api/dismiss-reminder', function (Request $request) {
        $user = Auth::user();
        abort_unless($user && ($user->isInstructor() || $user->isAdmin()), 403);

        $duration = (string) $request->input('duration', 'session');
        if ($duration === 'day') {
            session(['instructor_reminder_dismissed_until' => now()->endOfDay()->timestamp]);
        } else {
            session(['instructor_reminder_dismissed_session' => true]);
        }
        session(['instructor_reminder_shown_for_session' => true]);

        return response()->json(['status' => 'ok', 'duration' => $duration]);
    })->name('teach.dismiss-reminder');

    // Parent Account Switching to Child Learning Space
    Route::match(['GET', 'POST'], '/parent/switch-to-kid/{kid}', function (Request $request, User $kid) {
        $parent = Auth::user();
        if (! $parent) {
            return redirect()->route('login');
        }

        // Only linked parents or admins can switch
        $isLinked = $parent->kids()->where('users.id', $kid->id)->exists();
        if (! $isLinked && ! $parent->isAdmin()) {
            abort(403, 'You are only authorized to switch into your own children\'s accounts.');
        }

        if (! $kid->is_kid) {
            abort(400, 'Target user is not a child account.');
        }

        session(['impersonating_parent_id' => $parent->id]);
        Auth::login($kid);

        return redirect()->route('filament.student.pages.overview')
            ->with('status', 'Switched to ' . $kid->name . '\'s learning workspace.');
    })->name('parent.switch-to-kid');

    Route::match(['GET', 'POST'], '/parent/switch-back', function () {
        $parentId = session('impersonating_parent_id');
        if (! $parentId) {
            return redirect()->route('dashboard');
        }

        session()->forget('impersonating_parent_id');
        $parent = User::findOrFail($parentId);
        Auth::login($parent);

        return redirect()->route('filament.parent.pages.manage-kids')
            ->with('status', 'Returned to Parent Portal.');
    })->name('parent.switch-back');

    Route::prefix('student')->name('student.')->group(function () {
        Route::redirect('/overview', '/learn/overview')->name('overview');
        Route::redirect('/courses', '/learn/courses')->name('courses');
        Route::redirect('/schedule', '/learn/schedule')->name('schedule');
        Route::redirect('/assignments', '/learn/assignments')->name('assignments');
        Route::redirect('/assessments', '/learn/assessments')->name('assessments');
        Route::redirect('/materials', '/learn/materials')->name('materials');
    });

    Route::redirect('/schedule', '/learn/schedule');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::redirect('/overview', '/manage')->name('overview');
        Route::redirect('/students', '/manage/users')->name('students');
        Route::redirect('/courses', '/manage/courses')->name('courses');
        Route::redirect('/course-sessions', '/manage/course-sessions')->name('course-sessions');
        Route::redirect('/assignments', '/manage/assignments')->name('assignments');
        Route::redirect('/assessments', '/manage/assessments')->name('assessments');
        Route::redirect('/materials', '/manage/learning-materials')->name('materials');

        Route::get('/storage-check', function () {
            $user = Auth::user();
            abort_unless($user && ($user->isAdmin() || $user->isInstructor()), 403);

            $defaultDisk = config('filesystems.default');
            $publicDriver = config('filesystems.disks.public.driver');
            $publicBucket = config('filesystems.disks.public.bucket');
            $publicEndpoint = config('filesystems.disks.public.endpoint');

            $cloudEnvPresent = isset($_SERVER['LARAVEL_CLOUD_DISK_CONFIG']);
            $manualKeyPresent = ! empty(config('filesystems.disks.s3.key'));

            $writeSuccess = false;
            $readSuccess = false;
            $deleteSuccess = false;
            $error = null;

            $testFileName = '_healthcheck_' . time() . '.txt';
            $testContent = 'storage_ok_' . time();

            try {
                $testConfig = (array) config('filesystems.disks.public', []);
                $testConfig['throw'] = true;
                $testDisk = Storage::build($testConfig);

                $writeSuccess = $testDisk->put($testFileName, $testContent);
                if ($writeSuccess) {
                    $readContent = $testDisk->get($testFileName);
                    $readSuccess = ($readContent === $testContent);
                    $testDisk->delete($testFileName);
                    $deleteSuccess = true;
                }
            } catch (\Throwable $e) {
                $error = get_class($e) . ': ' . $e->getMessage();
            }

            return response()->json([
                'status' => ($writeSuccess && $readSuccess) ? 'CONNECTED_AND_WORKING' : 'STORAGE_FAILED',
                'storage' => [
                    'default_disk' => $defaultDisk,
                    'public_driver' => $publicDriver,
                    'public_bucket' => $publicBucket,
                    'public_endpoint' => $publicEndpoint,
                    'laravel_cloud_injected' => $cloudEnvPresent,
                    'manual_aws_vars_defined' => $manualKeyPresent,
                ],
                'operations' => [
                    'write' => $writeSuccess,
                    'read' => $readSuccess,
                    'delete' => $deleteSuccess,
                ],
                'error' => $error,
            ], ($writeSuccess && $readSuccess) ? 200 : 500);
        })->name('storage-check');
    });

    Route::middleware('auth')->group(function () {
        Route::get('/materials/{material}/read', \App\Livewire\MaterialReader::class)->name('materials.read');
        Route::get('/materials/{material}/watch', \App\Livewire\VideoPlayer::class)->name('materials.watch');
        Route::get('/materials/{material}', \App\Livewire\MaterialReader::class)->name('materials.show');
        Route::get('/lessons/{lesson}', \App\Livewire\VideoPlayer::class)->name('lessons.show');
        Route::get('/videos/{video}', \App\Livewire\VideoPlayer::class)->name('videos.show');
        Route::get('/sessions/{session}/view', \App\Livewire\SessionViewer::class)->name('sessions.view');
        Route::get('/sessions/{session}/watch', \App\Livewire\SessionViewer::class)->name('sessions.watch');
        Route::get('/sessions/{session}/read', \App\Livewire\SessionViewer::class)->name('sessions.read');
    });

    // Serve files from storage without requiring the storage:link symlink.
    Route::get('/file/view/{type}/{id}', function (Request $request, string $type, int $id) {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $disk = Storage::disk('public');
        $index = $request->query('index');
        $fileParam = $request->query('file');

        if ($type === 'material') {
            $material = ($user->isAdmin() || $user->isInstructor())
                ? LearningMaterial::query()->findOrFail($id)
                : LearningMaterial::query()->visibleTo($user)->findOrFail($id);
            $path = $material->file_path ?: $material->file_name;

            if ($user->role === 'student' || $user->isStudent()) {
                try {
                    app(\App\Services\GamificationService::class)->awardMaterialView($user, $material);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        } elseif ($type === 'assignment') {
            $assignment = ($user->isAdmin() || $user->isInstructor())
                ? Assignment::query()->findOrFail($id)
                : Assignment::query()->visibleTo($user)->findOrFail($id);
            $paths = $assignment->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $assignment->file_path;
            }
        } elseif ($type === 'assessment') {
            $assessment = ($user->isAdmin() || $user->isInstructor())
                ? Assessment::query()->findOrFail($id)
                : Assessment::query()->visibleTo($user)->findOrFail($id);
            $paths = $assessment->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $assessment->file_path;
            }
        } elseif ($type === 'submission') {
            $submission = AssignmentSubmission::query()->findOrFail($id);
            $canView = $user->isAdmin()
                || $user->isInstructor()
                || $submission->user_id === $user->id;
            abort_unless($canView, 403);
            $paths = $submission->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $submission->file_path;
            }
        } elseif ($type === 'assessment-submission') {
            $submission = AssessmentSubmission::query()->findOrFail($id);
            $canView = $user->isAdmin()
                || $user->isInstructor()
                || $submission->user_id === $user->id;
            abort_unless($canView, 403);
            $paths = $submission->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $submission->file_path;
            }
        } elseif ($type === 'chat-message') {
            $chatMessage = ChatMessage::query()->findOrFail($id);

            $isRoomMember = ChatRoom::query()
                ->whereKey($chatMessage->chat_room_id)
                ->whereHas('members', fn ($query) => $query->where('users.id', $user->id))
                ->exists();

            abort_unless($isRoomMember || $user->isAdmin(), 403);

            $attachments = $chatMessage->all_attachments;
            if ($index !== null && isset($attachments[(int) $index])) {
                $path = $attachments[(int) $index]['path'];
            } elseif ($fileParam) {
                $matched = collect($attachments)->firstWhere('path', $fileParam);
                $path = $matched ? $matched['path'] : ($attachments[0]['path'] ?? $chatMessage->attachment_path);
            } else {
                $path = $attachments[0]['path'] ?? $chatMessage->attachment_path;
            }
        } elseif ($type === 'instructor-application') {
            $application = InstructorApplication::query()->findOrFail($id);
            $canAccess = $user->isAdmin()
                || ($application->user_id && $application->user_id === $user->id)
                || (filled($application->email) && strtolower((string) $application->email) === strtolower((string) $user->email));
            abort_unless($canAccess, 403);

            $doc = $request->query('doc') ?: $request->query('attachment');
            $paths = array_filter([
                'cv' => $application->cv_path,
                'roadmap' => $application->roadmap_path,
                'full_roadmap' => $application->full_roadmap_path,
                'curriculum' => $application->curriculum_path,
            ]);

            if ($doc && isset($paths[$doc])) {
                $path = $paths[$doc];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths['cv'] ?? reset($paths);
            }
        } elseif ($type === 'session-reading' || $type === 'session-video') {
            $session = \App\Models\CourseSession::query()->findOrFail($id);
            $canView = $user->isAdmin() || $user->isInstructor() || $user->isEnrolledInCourse($session->course_id);
            abort_unless($canView, 403);
            $path = $type === 'session-reading' ? $session->reading_file_path : $session->video_file_path;
        } else {
            abort(404);
        }

        $downloadName = $request->query('name') ?: basename((string) $path);
        $isDownload = (bool) $request->query('download');

        return \App\Support\DocumentResponse::serve(
            rawPath: $path,
            downloadName: $downloadName,
            isDownload: $isDownload,
            extraHeaders: [
                'X-Frame-Options' => 'SAMEORIGIN',
                'Content-Security-Policy' => "frame-ancestors 'self'",
            ]
        );
    })->name('file.view');

    Route::get('/file/download/{type}/{id}', function (Request $request, string $type, int $id) {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $disk = Storage::disk('public');
        $index = $request->query('index');
        $fileParam = $request->query('file');
        $downloadName = null;

        if ($type === 'material') {
            $material = ($user->isAdmin() || $user->isInstructor())
                ? LearningMaterial::query()->findOrFail($id)
                : LearningMaterial::query()->visibleTo($user)->findOrFail($id);
            $path = $material->file_path ?: $material->file_name;
            $downloadName = $material->file_name ?: ($material->title ? $material->title . '.' . pathinfo($path, PATHINFO_EXTENSION) : basename($path));
        } elseif ($type === 'assignment') {
            $assignment = ($user->isAdmin() || $user->isInstructor())
                ? Assignment::query()->findOrFail($id)
                : Assignment::query()->visibleTo($user)->findOrFail($id);
            $paths = $assignment->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $assignment->file_path;
            }
            $downloadName = basename($path);
        } elseif ($type === 'assessment') {
            $assessment = ($user->isAdmin() || $user->isInstructor())
                ? Assessment::query()->findOrFail($id)
                : Assessment::query()->visibleTo($user)->findOrFail($id);
            $paths = $assessment->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $assessment->file_path;
            }
            $downloadName = basename($path);
        } elseif ($type === 'submission') {
            $submission = AssignmentSubmission::query()->findOrFail($id);
            $canView = $user->isAdmin()
                || $user->isInstructor()
                || $submission->user_id === $user->id;
            abort_unless($canView, 403);
            $paths = $submission->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $submission->file_path;
            }
            $downloadName = basename($path);
        } elseif ($type === 'assessment-submission') {
            $submission = AssessmentSubmission::query()->findOrFail($id);
            $canView = $user->isAdmin()
                || $user->isInstructor()
                || $submission->user_id === $user->id;
            abort_unless($canView, 403);
            $paths = $submission->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $submission->file_path;
            }
            $downloadName = basename($path);
        } elseif ($type === 'chat-message') {
            $chatMessage = ChatMessage::query()->findOrFail($id);

            $isRoomMember = ChatRoom::query()
                ->whereKey($chatMessage->chat_room_id)
                ->whereHas('members', fn ($query) => $query->where('users.id', $user->id))
                ->exists();

            abort_unless($isRoomMember || $user->isAdmin(), 403);

            $attachments = $chatMessage->all_attachments;
            if ($index !== null && isset($attachments[(int) $index])) {
                $path = $attachments[(int) $index]['path'];
                $downloadName = $attachments[(int) $index]['name'] ?? basename($path);
            } elseif ($fileParam) {
                $matched = collect($attachments)->firstWhere('path', $fileParam);
                $path = $matched ? $matched['path'] : ($attachments[0]['path'] ?? $chatMessage->attachment_path);
                $downloadName = $matched['name'] ?? ($attachments[0]['name'] ?? ($chatMessage->attachment_name ?: basename($path)));
            } else {
                $path = $attachments[0]['path'] ?? $chatMessage->attachment_path;
                $downloadName = $attachments[0]['name'] ?? ($chatMessage->attachment_name ?: basename($path));
            }
        } elseif ($type === 'instructor-application') {
            $application = InstructorApplication::query()->findOrFail($id);
            $canAccess = $user->isAdmin()
                || ($application->user_id && $application->user_id === $user->id)
                || (filled($application->email) && strtolower((string) $application->email) === strtolower((string) $user->email));
            abort_unless($canAccess, 403);

            $doc = $request->query('doc') ?: $request->query('attachment');
            $paths = array_filter([
                'cv' => $application->cv_path,
                'roadmap' => $application->roadmap_path,
                'full_roadmap' => $application->full_roadmap_path,
                'curriculum' => $application->curriculum_path,
            ]);

            if ($doc && isset($paths[$doc])) {
                $path = $paths[$doc];
                $docKey = $doc;
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
                $docKey = array_search($fileParam, $paths, true);
            } else {
                $path = $paths['cv'] ?? reset($paths);
                $docKey = 'cv';
            }

            $applicantSlug = Str::slug($application->name ?: 'Applicant', '_');
            $ext = pathinfo((string) $path, PATHINFO_EXTENSION);
            $extSuffix = $ext ? '.' . $ext : '';
            $downloadName = "{$applicantSlug}_{$docKey}{$extSuffix}";
        } else {
            abort(404);
        }

        $downloadName = $downloadName ?: basename((string) $path);

        return \App\Support\DocumentResponse::serve(
            rawPath: $path,
            downloadName: $downloadName,
            isDownload: true
        );
    })->name('file.download');

    // Signed URL route for viewing Office documents via Google Docs Viewer.
    // Generates a temporary signed URL that doesn't require authentication.
    Route::get('/file/signed/{type}/{id}', function (Request $request, string $type, int $id) {
        $user = Auth::user();
        if (! $user) {
            abort(403);
        }

        $disk = Storage::disk('public');
        $index = $request->query('index');
        $fileParam = $request->query('file');

        if ($type === 'material') {
            $material = ($user->isAdmin() || $user->isInstructor())
                ? LearningMaterial::query()->findOrFail($id)
                : LearningMaterial::query()->visibleTo($user)->findOrFail($id);
            $path = $material->file_path;

            if ($user->role === 'student' || $user->isStudent()) {
                try {
                    app(\App\Services\GamificationService::class)->awardMaterialView($user, $material);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        } elseif ($type === 'assignment') {
            $assignment = ($user->isAdmin() || $user->isInstructor())
                ? Assignment::query()->findOrFail($id)
                : Assignment::query()->visibleTo($user)->findOrFail($id);
            $paths = $assignment->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $assignment->file_path;
            }
        } elseif ($type === 'assessment') {
            $assessment = ($user->isAdmin() || $user->isInstructor())
                ? Assessment::query()->findOrFail($id)
                : Assessment::query()->visibleTo($user)->findOrFail($id);
            $paths = $assessment->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $assessment->file_path;
            }
        } elseif ($type === 'submission') {
            $submission = AssignmentSubmission::query()->findOrFail($id);
            $canView = $user->isAdmin()
                || $user->isInstructor()
                || $submission->user_id === $user->id;
            abort_unless($canView, 403);
            $paths = $submission->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $submission->file_path;
            }
        } elseif ($type === 'assessment-submission') {
            $submission = AssessmentSubmission::query()->findOrFail($id);
            $canView = $user->isAdmin()
                || $user->isInstructor()
                || $submission->user_id === $user->id;
            abort_unless($canView, 403);
            $paths = $submission->all_file_paths;
            if ($index !== null && isset($paths[(int) $index])) {
                $path = $paths[(int) $index];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths[0] ?? $submission->file_path;
            }
        } elseif ($type === 'instructor-application') {
            $application = InstructorApplication::query()->findOrFail($id);
            $canAccess = $user->isAdmin()
                || ($application->user_id && $application->user_id === $user->id)
                || (filled($application->email) && strtolower((string) $application->email) === strtolower((string) $user->email));
            abort_unless($canAccess, 403);

            $doc = $request->query('doc') ?: $request->query('attachment');
            $paths = array_filter([
                'cv' => $application->cv_path,
                'roadmap' => $application->roadmap_path,
                'full_roadmap' => $application->full_roadmap_path,
                'curriculum' => $application->curriculum_path,
            ]);

            if ($doc && isset($paths[$doc])) {
                $path = $paths[$doc];
            } elseif ($fileParam && in_array($fileParam, $paths, true)) {
                $path = $fileParam;
            } else {
                $path = $paths['cv'] ?? reset($paths);
            }
        } else {
            abort(404);
        }

        [$resolvedDisk, $resolvedPath] = \App\Support\DocumentResponse::resolveFile($path);

        if (! $resolvedPath || ! $resolvedDisk) {
            abort(404);
        }

        $signedUrl = URL::temporarySignedRoute(
            'file.public',
            now()->addMinutes(30),
            ['path' => $resolvedPath]
        );

        return response()->json(['url' => $signedUrl]);
    })->name('file.signed');

    Route::get('/claim-hub', fn () => redirect()->to('/student/claim-hub'))->name('claim-hub');

    // Admin Report Generation Routes
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/student/{student}', [\App\Http\Controllers\ReportController::class, 'studentReport'])->name('student');
        Route::get('/course/{course}', [\App\Http\Controllers\ReportController::class, 'courseReport'])->name('course');
        Route::get('/intake/{intake}', [\App\Http\Controllers\ReportController::class, 'intakeReport'])->name('intake');
        Route::get('/instructor-application/{application}', [\App\Http\Controllers\ReportController::class, 'instructorApplicationReport'])->name('instructor-application');
        Route::get('/instructor-application/{application}/attachment/{type}', [\App\Http\Controllers\ReportController::class, 'instructorApplicationAttachment'])->name('instructor-application.attachment');
        Route::get('/instructor-application/{application}/attachments-zip', [\App\Http\Controllers\ReportController::class, 'instructorApplicationAttachmentsZip'])->name('instructor-application.attachments-zip');
    });
});

// Publicly accessible signed route for Google Docs Viewer to fetch the file.
Route::get('/file/public', function (Request $request) {
    if (! $request->hasValidSignature()) {
        abort(403);
    }

    $path = PublicDiskPath::normalize((string) $request->query('path', ''));

    // Prevent path traversal attacks.
    if (! $path || str_contains($path, '..')) {
        abort(404);
    }

    return \App\Support\DocumentResponse::serve(
        rawPath: $path,
        downloadName: basename($path),
        isDownload: false
    );
})->name('file.public');

// Sequential E-Learning Classroom & Course Player
Route::middleware(['auth'])->group(function () {
    Route::get('/portal/dashboard', function () {
        return redirect()->route('filament.student.pages.overview');
    })->name('portal.dashboard');

    Route::get('/learn/{course:slug}/{lessonId?}', \App\Livewire\CoursePlayer::class)
        ->name('course.player')
        ->where('course', '^(?!courses|assessments|assignments|certificates|claim-hub|community|learning-resources|login|logout|materials|opportunities|overview|quizzes|reviews|schedule|search|settings|take-quiz).*$');

    Route::get('/classroom/{course:slug}/{lessonId?}', \App\Livewire\CoursePlayer::class)
        ->name('course.classroom');
});

require __DIR__.'/auth.php';

Route::domain('www.thinker.it.com')->group(function () {
    Route::get('/{path?}', function (Request $request, ?string $path = '') {
        $target = 'https://thinker.it.com/'.ltrim((string) $path, '/');
        $query = $request->getQueryString();

        if ($query) {
            $target .= '?'.$query;
        }

        return redirect()->to($target, 301);
    })->where('path', '.*');
});
