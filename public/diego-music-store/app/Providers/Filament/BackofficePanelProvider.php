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
            ->maxContentWidth('full')
            ->colors([
                'primary' => Color::Blue,
            ])
            ->brandName(function () {
                $branchId = \App\Helpers\BranchHelper::getActiveBranchId();
                $branch = \App\Models\Branch::find($branchId);
                return $branch ? ($branch->store_name ?: "Diego Music Store ({$branch->name})") : 'Diego Music Store';
            })
            ->brandLogo(fn () => view('filament.components.brand-logo'))
            ->brandLogoHeight('auto')
            ->favicon(asset('favicon.ico'))
            ->navigationGroups([
                NavigationGroup::make()
                     ->label('Shop')
                     ->icon('heroicon-o-shopping-cart'),
                NavigationGroup::make()
                    ->label('Blog')
                    ->icon('heroicon-o-pencil'),
                NavigationGroup::make()
                    ->label('Master Data')
                    ->icon('heroicon-o-circle-stack'),
                NavigationGroup::make()
                    ->label('Manajemen Karyawan')
                    ->icon('heroicon-o-user-group'),
                NavigationGroup::make()
                     ->label('Pembelian')
                     ->icon('heroicon-o-shopping-bag'),
                NavigationGroup::make()
                     ->label('Penjualan')
                     ->icon('heroicon-o-credit-card'),
                NavigationGroup::make()
                     ->label('Inventori')
                     ->icon('heroicon-o-archive-box'),
                NavigationGroup::make()
                    ->label('Kelola User')
                    ->icon('heroicon-o-users'),
                NavigationGroup::make()
                     ->label('Akuntansi')
                     ->icon('heroicon-o-banknotes'),
                NavigationGroup::make()
                     ->label('Laporan Keuangan')
                     ->icon('heroicon-o-document-chart-bar'),
                NavigationGroup::make()
                    ->label('Pengaturan')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->sidebarCollapsibleOnDesktop()
            ->navigationItems([
                \Filament\Navigation\NavigationItem::make('Point of Sale')
                    ->url('/pos')
                    ->icon('heroicon-o-shopping-cart')
                    ->group('Penjualan')
                    ->sort(1),
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                AccountWidget::class,
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
            PanelsRenderHook::HEAD_START,
            fn (): HtmlString => new HtmlString(
                view('filament.components.favicons')->render()
            ),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::USER_MENU_BEFORE,
            fn (): HtmlString => new HtmlString(
                view('filament.components.branch-switcher-topbar')->render()
            ),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIMPLE_PAGE_START,
            fn (): HtmlString => new HtmlString(
                view('filament.components.login-header-extra')->render()
            ),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::SIMPLE_LAYOUT_END,
            fn (): HtmlString => new HtmlString(
                view('filament.components.login-footer')->render()
            ),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): HtmlString => new HtmlString(
                view('filament.components.custom-styles')->render()
            ),
        );
    }
}
