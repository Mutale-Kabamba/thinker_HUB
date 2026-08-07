<?php

namespace App\Providers;

use App\Http\Responses\FilamentLogoutResponse;
use App\Models\Assessment;
use App\Models\AssessmentSubmission;
use App\Models\Assignment;
use App\Models\AssignmentSubmission;
use App\Models\ChatMessage;
use App\Models\Course;
use App\Models\CourseSession;
use App\Models\Enrollment;
use App\Models\LearningMaterial;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use App\Observers\AssignmentObserver;
use App\Observers\ChatMessageObserver;
use App\Observers\CourseSessionObserver;
use App\Observers\LearningMaterialObserver;
use App\Observers\QuizAttemptObserver;
use App\Observers\SubmissionObserver;
use App\Observers\UserObserver;
use App\Policies\AssessmentPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\CoursePolicy;
use App\Policies\EnrollmentPolicy;
use App\Policies\LearningMaterialPolicy;
use App\Policies\QuizPolicy;
use App\Policies\UserPolicy;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Mail\Events\MessageSending;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Assignment::observe(AssignmentObserver::class);
        AssignmentSubmission::observe(SubmissionObserver::class);
        AssessmentSubmission::observe(SubmissionObserver::class);
        ChatMessage::observe(ChatMessageObserver::class);
        CourseSession::observe(CourseSessionObserver::class);
        LearningMaterial::observe(LearningMaterialObserver::class);
        QuizAttempt::observe(QuizAttemptObserver::class);
        User::observe(UserObserver::class);

        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Enrollment::class, EnrollmentPolicy::class);
        Gate::policy(Course::class, CoursePolicy::class);
        Gate::policy(Assignment::class, AssignmentPolicy::class);
        Gate::policy(LearningMaterial::class, LearningMaterialPolicy::class);
        Gate::policy(Assessment::class, AssessmentPolicy::class);
        Gate::policy(Quiz::class, QuizPolicy::class);

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
