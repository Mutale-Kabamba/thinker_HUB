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
use Illuminate\View\Middleware\ShareErrorsFromSession;

class ContributorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('contributor')
            ->path('contribute')
            ->brandName(fn (): string => match (auth()->user()?->role) {
                'blogger' => 'Thinker HUB • Blogger Workspace',
                'researcher' => 'Thinker HUB • Researcher Workspace',
                'employer' => 'Thinker HUB • Employer Workspace',
                default => 'Thinker HUB • Contributor Portal',
            })
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
            ->sidebarCollapsibleOnDesktop()
            ->databaseNotifications()
            ->databaseNotificationsPolling(null)
            ->errorNotifications(false)
            ->navigationGroups([
                'CONTRIBUTIONS',
                'ACCOUNT',
            ])
            ->discoverResources(in: app_path('Filament/Contributor/Resources'), for: 'App\\Filament\\Contributor\\Resources')
            ->discoverPages(in: app_path('Filament/Contributor/Pages'), for: 'App\\Filament\\Contributor\\Pages')
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
                    ->label('Instructor Hub')
                    ->icon('heroicon-o-academic-cap')
                    ->url('/teach')
                    ->visible(fn (): bool => (bool) (auth()->user()?->canSwitchToInstructor())),
                MenuItem::make()
                    ->label('Profile Management')
                    ->icon('heroicon-o-user-circle')
                    ->url(fn (): string => route('filament.contributor.pages.settings')),
            ])
            ->discoverWidgets(in: app_path('Filament/Contributor/Widgets'), for: 'App\\Filament\\Contributor\\Widgets')
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
                    'action' => '',
                ])->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): \Illuminate\Support\HtmlString => new \Illuminate\Support\HtmlString('
                    <script>
                        document.addEventListener("livewire:init", () => {
                            Livewire.hook("request", ({ fail }) => {
                                fail(({ status, preventDefault }) => {
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
