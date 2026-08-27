<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Pages\SharedLogin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class InstructorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('instructor')
            ->path('teach')
            ->brandName('Thinker HUB • Instructor Workspace')
            ->brandLogo(asset('images/logos/green.png'))
            ->darkModeBrandLogo(asset('images/logos/yellow_white.png'))
            ->brandLogoHeight('2.1rem')
            ->login(SharedLogin::class)
            ->colors([
                'primary' => Color::Teal,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->font('Plus Jakarta Sans', url: 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap')
            ->databaseNotifications()
            ->databaseNotificationsPolling(null)
            ->errorNotifications(false)
            ->navigationGroups([
                'ACADEMICS & CONTENT',
                'GRADING & EVALUATIONS',
                'PEOPLE & ROLES',
                'COMMUNITY & SYSTEM',
            ])
            ->discoverResources(in: app_path('Filament/Instructor/Resources'), for: 'App\Filament\Instructor\Resources')
            ->discoverPages(in: app_path('Filament/Instructor/Pages'), for: 'App\Filament\Instructor\Pages')
            ->userMenuItems([
                MenuItem::make()
                    ->label('Admin Portal')
                    ->icon('heroicon-o-shield-check')
                    ->url('/manage')
                    ->visible(fn (): bool => (bool) (auth()->user()?->canSwitchToAdmin())),
                MenuItem::make()
                    ->label('Student Workspace')
                    ->icon('heroicon-o-book-open')
                    ->url('/learn')
                    ->visible(fn (): bool => (bool) (auth()->user()?->canSwitchToStudent())),
                MenuItem::make()
                    ->label('Contributor Desk')
                    ->icon('heroicon-o-sparkles')
                    ->url('/contribute')
                    ->visible(fn (): bool => (bool) (auth()->user()?->canSwitchToContributor())),
                MenuItem::make()
                    ->label('Profile Management')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (): string => route('filament.instructor.pages.settings')),
            ])
            ->discoverWidgets(in: app_path('Filament/Instructor/Widgets'), for: 'App\Filament\Instructor\Widgets')
            ->widgets([
                AccountWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::BODY_START,
                fn (): string => view('partials.app-preloader')->render(),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.partials.panel-theme')->render(),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('partials.pwa-register')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn (): string => view('filament.partials.workspace-badge', ['position' => 'sidebar'])->render(),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): string => view('filament.partials.top-search', [
                    'action' => route('filament.instructor.pages.search'),
                ])->render(),
            )
            // FIX 2: Gracefully handle expired CSRF/sessions on background calls
            ->renderHook(
                        PanelsRenderHook::BODY_END,
                        fn (): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString('
                            <script>
                                document.addEventListener("livewire:init", () => {
                                    Livewire.hook("request", ({ fail }) => {
                                        fail(({ status, preventDefault }) => {
                                // Prevent popup on connection closed, timeout, or session expiry
                                if (status === null || status === 0 || status === 419 || status === 401) {
                                    preventDefault();
                                    if (status === 419 || status === 401) {
                                        window.location.reload();
                                    }
                                }
                            });
                        });
                    });
                </script>
            ')
        )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                EnsureEmailIsVerified::class,
            ]);
    }
}