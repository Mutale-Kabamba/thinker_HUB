<?php

namespace App\Providers\Filament;

use App\Filament\Auth\Pages\SharedLogin;
use App\Filament\Widgets\AdminStatsWidget;
use App\Filament\Widgets\RecentActivitiesWidget;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\MenuItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('manage')
            ->login(SharedLogin::class)
            ->colors([
                'primary' => Color::Teal,
            ])
            ->databaseNotifications()
            ->databaseNotificationsPolling(null) // FIX 1: Turn off background polling for notifications
            ->errorNotifications(false)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->navigationGroups([
                'ACADEMICS & CONTENT',
                'GRADING & EVALUATIONS',
                'PEOPLE & ROLES',
                'COMMUNITY & SYSTEM',
            ])
            ->pages([
                Dashboard::class,
            ])
            ->userMenuItems([
                MenuItem::make()
                    ->label('Profile Management')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (): string => route('filament.admin.pages.settings')),
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AdminStatsWidget::class,
                RecentActivitiesWidget::class,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('filament.partials.panel-theme')->render(),
            )
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): string => view('partials.pwa-register')->render(),
            )
            ->renderHook(
                PanelsRenderHook::TOPBAR_END,
                fn (): string => view('filament.partials.top-search', [
                    'action' => route('filament.admin.pages.search'),
                ])->render(),
            )
            // FIX 2: Handle expired CSRF tokens / sessions cleanly instead of showing page load errors
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