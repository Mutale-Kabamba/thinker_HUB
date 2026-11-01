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

class ParentPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('parent')
            ->path('parent')
            ->brandName('Thinker HUB • Parents Portal')
            ->brandLogo(asset('images/logos/green.png'))
            ->darkModeBrandLogo(asset('images/logos/yellow_white.png'))
            ->brandLogoHeight('2.1rem')
            ->login(SharedLogin::class)
            ->colors([
                'primary' => Color::Indigo,
                'gray' => Color::Slate,
                'info' => Color::Sky,
                'success' => Color::Emerald,
                'warning' => Color::Amber,
                'danger' => Color::Rose,
            ])
            ->font('Plus Jakarta Sans', url: 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap')
            ->spa()
            ->databaseNotifications()
            ->databaseNotificationsPolling(null)
            ->errorNotifications(false)
            ->discoverResources(in: app_path('Filament/Parent/Resources'), for: 'App\Filament\Parent\Resources')
            ->discoverPages(in: app_path('Filament/Parent/Pages'), for: 'App\Filament\Parent\Pages')
            ->userMenuItems([
                MenuItem::make()
                    ->label('Admin Portal')
                    ->icon('heroicon-o-shield-check')
                    ->url('/manage')
                    ->visible(fn (): bool => (bool) (auth()->user()?->canSwitchToAdmin())),
                MenuItem::make()
                    ->label('Instructor Hub')
                    ->icon('heroicon-o-academic-cap')
                    ->url('/teach')
                    ->visible(fn (): bool => (bool) (auth()->user()?->canSwitchToInstructor())),
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
                    ->label('Parent Settings')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->url(fn (): string => route('filament.parent.pages.parent-settings')),
            ])
            ->discoverWidgets(in: app_path('Filament/Parent/Widgets'), for: 'App\Filament\Parent\Widgets')
            ->navigationGroups([
                'KIDS MANAGEMENT',
                'ACADEMICS & PROGRESS',
                'PARENT COMMUNITY',
                'SYSTEM',
            ])
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
                PanelsRenderHook::HEAD_END,
                fn (): string => view('partials.echo')->render(),
            )
            ->renderHook(
                PanelsRenderHook::SIDEBAR_NAV_START,
                fn (): string => view('filament.partials.workspace-badge', [
                    'position' => 'sidebar',
                    'label' => 'Parents Portal',
                ])->render(),
            )
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): HtmlString => new HtmlString('
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
