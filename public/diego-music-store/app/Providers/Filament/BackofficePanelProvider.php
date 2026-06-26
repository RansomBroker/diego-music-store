<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Filament\Widgets\FilamentInfoWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\HtmlString;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class BackofficePanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('backoffice')
            ->path('backoffice')
            ->login(\App\Filament\Pages\Auth\CustomLogin::class)
            ->colors([
                'primary' => Color::Blue,
            ])
            ->navigationGroups([
                NavigationGroup::make()
                     ->label('Shop')
                     ->icon('heroicon-o-shopping-cart'),
                NavigationGroup::make()
                    ->label('Blog')
                    ->icon('heroicon-o-pencil'),
                NavigationGroup::make()
                    ->label('Master Data'),
                NavigationGroup::make()
                    ->label('Kelola User'),
                NavigationGroup::make()
                    ->label(fn (): string => __('navigation.settings'))
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsed(),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
                FilamentInfoWidget::class,
            ])
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
            ]);
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): HtmlString => new HtmlString('
                <style>
                    /* Custom Sidebar Styles */
                    .fi-sidebar {
                        border-right: 1px solid rgb(226, 232, 240) !important;
                        background-color: rgb(241, 245, 249) !important;
                        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.02) !important;
                        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
                    }
                    .dark .fi-sidebar {
                        border-right: 1px solid rgb(30, 41, 59) !important;
                        background-color: rgb(15, 23, 42) !important;
                        box-shadow: 2px 0 8px rgba(0, 0, 0, 0.2) !important;
                    }
                    .fi-sidebar-header {
                        border-bottom: 1px solid rgb(226, 232, 240) !important;
                    }
                    .dark .fi-sidebar-header {
                        border-bottom: 1px solid rgb(30, 41, 59) !important;
                    }
                </style>
            '),
        );
    }
}
