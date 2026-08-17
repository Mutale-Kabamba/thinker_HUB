<?php

namespace App\Providers;

use App\Http\Responses\FilamentLogoutResponse;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\Attendance;
use App\Models\ChatMessage;
use App\Models\Course;
use App\Models\CourseRating;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\Friendship;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Review;
use App\Models\User;
use App\Observers\AssessmentObserver;
use App\Observers\AssignmentObserver;
use App\Observers\AttendanceObserver;
use App\Observers\ChatMessageObserver;
use App\Observers\CourseRatingObserver;
use App\Observers\CourseSessionObserver;
use App\Observers\FriendshipObserver;
use App\Observers\LearningMaterialObserver;
use App\Observers\QuizAttemptObserver;
use App\Observers\ReviewObserver;
use App\Observers\SubmissionObserver;
use App\Observers\UserObserver;
use App\Policies\AssessmentPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\CoursePolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\LearningMaterialPolicy;
use App\Policies\QuizPolicy;
use App\Policies\UserPolicy;
use App\Services\GamificationService;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Auth\Events\Login;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LogoutResponseContract::class, FilamentLogoutResponse::class);
        $this->app->singleton(\Laravel\Passkeys\Contracts\PasskeyLoginResponse::class, \App\Http\Responses\PasskeyLoginResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Dynamically align WebAuthn Relying Party ID and Allowed Origins with current request
        if (! $this->app->runningInConsole()) {
            try {
                $host = request()->getHost();
                if ($host) {
                    config(['passkeys.relying_party_id' => env('PASSKEYS_RP_ID') ?: $host]);

                    $origins = config('passkeys.allowed_origins', []);
                    $schemeAndHost = request()->getSchemeAndHttpHost();
                    $origins[] = $schemeAndHost;
                    $origins[] = 'https://' . $host;
                    $origins[] = 'http://' . $host;
                    config(['passkeys.allowed_origins' => array_values(array_unique(array_filter($origins)))]);
                }
            } catch (\Throwable) {
                // Ignore during early bootstrap
            }
        }

        Assessment::observe(AssessmentObserver::class);
        Assignment::observe(AssignmentObserver::class);
        AssignmentSubmission::observe(SubmissionObserver::class);
        AssessmentSubmission::observe(SubmissionObserver::class);
        Attendance::observe(AttendanceObserver::class);
        ChatMessage::observe(ChatMessageObserver::class);
        CourseRating::observe(CourseRatingObserver::class);
        CourseSession::observe(CourseSessionObserver::class);
        Friendship::observe(FriendshipObserver::class);
        LearningMaterial::observe(LearningMaterialObserver::class);
        Quiz::observe(\App\Observers\QuizObserver::class);
        QuizAttempt::observe(QuizAttemptObserver::class);
        Review::observe(ReviewObserver::class);
        User::observe(UserObserver::class);

        Event::listen(Login::class, function (Login $event): void {
            if ($event->user instanceof User) {
                try {
                    app(GamificationService::class)->recordDailyLogin($event->user);
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        });

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Enrollment::class, EnrollmentPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(LearningMaterial::class, LearningMaterialPolicy::class);
        Gate::policy(Assessment::class, AssessmentPolicy::class);
        Gate::policy(Quiz::class, QuizPolicy::class);

        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $resetUrl = url(route('password.reset', [
                'token' => $token,
                'email' => $notifiable->getEmailForPasswordReset(),
            ], false));

            $rawName = trim((string) ($notifiable->name ?? ''));
            $firstName = $rawName !== '' ? (explode(' ', $rawName)[0] ?? $rawName) : '';
            $greeting = $firstName !== '' ? "Hello {$firstName}!" : "Hello!";

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Thinker HUB: Reset Password')
                ->greeting($greeting)
                ->line('You are receiving this email because we received a password reset request for your account.')
                ->action('Reset Password', $resetUrl)
                ->line('This password reset link will expire in ' . config('auth.passwords.'.config('auth.defaults.passwords').'.expire') . ' minutes.')
                ->line('If you did not request a password reset, no further action is required.')
                ->salutation("Regards,\n" . config('app.name', 'Thinker HUB'));
        });

        $this->configureMailDeliverabilityHeaders();
        $this->configureMailSslPeerName();
    }

    private function configureMailDeliverabilityHeaders(): void
    {
        $messageIdDomain = trim((string) config('mail.deliverability.message_id_domain', 'oristudiozm.com'));

        $this->app['events']->listen(MessageSending::class, function (MessageSending $event) use ($messageIdDomain): void {
            $headers = $event->message->getHeaders();

            if ($messageIdDomain !== '' && ! $headers->has('Message-ID') && method_exists($headers, 'addIdHeader')) {
                $headers->addIdHeader('Message-ID', (string) Str::uuid().'@'.$messageIdDomain);
            }
        });
    }

    /**
     * Override the SMTP transport SSL peer name when the hosting certificate
     * does not match the mail hostname (common on Namecheap shared hosting).
     */
    private function configureMailSslPeerName(): void
    {
        $peerName = config('mail.mailers.smtp.tls.peer_name');

        if (! $peerName) {
            return;
        }

        $this->app->afterResolving('mail.manager', function ($manager) use ($peerName) {
            try {
                $transport = $manager->mailer('smtp')->getSymfonyTransport();

                if (method_exists($transport, 'getStream')) {
                    $stream = $transport->getStream();

                    if (method_exists($stream, 'setStreamOptions')) {
                        $stream->setStreamOptions([
                            'ssl' => [
                                'verify_peer' => true,
                                'verify_peer_name' => true,
                                'peer_name' => $peerName,
                            ],
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // Silently skip if transport not yet available.
            }
        });
    }
}
