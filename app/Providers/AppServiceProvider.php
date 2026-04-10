<?php

namespace App\Providers;

use App\Http\Responses\FilamentLogoutResponse;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\User;
use App\Observers\AssignmentObserver;
use App\Observers\LearningMaterialObserver;
use App\Policies\AssessmentPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\CoursePolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\LearningMaterialPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, FilamentLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Assignment::observe(AssignmentObserver::class);
        LearningMaterial::observe(LearningMaterialObserver::class);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Enrollment::class, EnrollmentPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(LearningMaterial::class, LearningMaterialPolicy::class);
        Gate::policy(Assessment::class, AssessmentPolicy::class);
    }
}
