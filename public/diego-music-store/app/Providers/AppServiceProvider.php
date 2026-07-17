<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            // Silence migration errors
        }

        \Illuminate\Support\Facades\Blade::anonymousComponentPath(
            resource_path('views/filament/pages/pos/components'),
            'pos-page'
        );
    }
}
