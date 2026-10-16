<?php

namespace App\Providers\Filament;

use App\Filament\Livewire\DatabaseNotifications;
use App\Filament\Pages\Auth\Login;
use App\Http\Controllers\OrderPrintController;
use App\Models\GeneralSetting;
use Asignua\FilamentActivityLogPlus\ActivityLogPlusPlugin;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Width;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Prodstarter\FilamentNotificationCenter\FilamentNotificationCenterPlugin;
use Prodstarter\FilamentNotificationCenter\NotificationCenterCategory;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('dashboard')
            ->path('dashboard')
            ->authenticatedRoutes(function (): void {
                Route::get('orders/{order}/print', OrderPrintController::class)
                    ->name('orders.print');
            })
            ->maxContentWidth(Width::Full)
            ->topbar(true)
            ->databaseNotifications(livewireComponent: DatabaseNotifications::class)
            ->viteTheme('resources/css/filament/dashboard/theme.css')

            ->brandLogo(fn (): ?string => ($logo = GeneralSetting::query()->value('site_logo')) && Storage::disk('public')->exists($logo)
                ? Storage::disk('public')->url($logo)
                : null)
            ->brandLogoHeight('3rem')
            ->sidebarCollapsibleOnDesktop()
            ->spa()
            ->login(Login::class)

            ->colors([
                'primary' => Color::Blue,

            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->navigationGroups([
                'Products',
                'Orders',
                'Purchases',
                'Peoples',
                'Settings',

            ])
            ->discoverPages(
                in: app_path('Filament/Pages'),
                for: 'App\Filament\Pages'
            )
            ->pages([])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                // AccountWidget::class,
                // FilamentInfoWidget::class,
            ])
            ->plugins([
                FilamentNotificationCenterPlugin::make()->categories([
                    NotificationCenterCategory::make('low-stock')->label('Low stock')->order(1),
                    NotificationCenterCategory::make('orders')->label('New orders')->order(2),
                ]),
                ActivityLogPlusPlugin::make()
                    ->navigationGroup('Settings')
                    ->authorizeResource(fn (): bool => Filament::auth()->user()?->can('ViewAny:Activity') ?? false)
                    ->authorizeHistory(fn (): bool => Filament::auth()->user()?->can('ViewAny:Activity') ?? false),
                FilamentShieldPlugin::make()
                    ->navigationGroup('Settings'),
            ])
            ->databaseNotificationsLivewireComponent(DatabaseNotifications::class)
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            // ->plugin(FilamentShieldPlugin::make())
            ->resourceCreatePageRedirect('index')
            ->resourceEditPageRedirect('index')
            ->authMiddleware([
                Authenticate::class,

            ]);
    }
}
