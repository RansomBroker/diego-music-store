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
        if ($this->app->runningInConsole()) {
            $this->commands([
                \App\Console\Commands\ProcessScheduledJournals::class,
                \App\Console\Commands\ExecuteYearEndClosingCommand::class,
            ]);
        }

        // Auto-migrate is intentionally removed because it severely impacts performance if run on every HTTP request.
        // Migrations should only be run during deployment or via CLI.

        \Illuminate\Support\Facades\Blade::anonymousComponentPath(
            resource_path('views/filament/pages/pos/components'),
            'pos-page'
        );

        \Filament\Forms\Components\TextInput::macro('rupiah', function (string|bool|null $prefix = 'Rp', int $precision = 0) {
            /** @var \Filament\Forms\Components\TextInput $this */
            $component = $this;

            if ($prefix !== false && $prefix !== null) {
                $component->prefix($prefix);
            }

            $component->currencyMask(
                thousandSeparator: '.',
                decimalSeparator: ',',
                precision: $precision
            );

            $component->dehydrateStateUsing(fn ($state) => \App\Helpers\FormatHelper::parseRupiah($state));

            return $component;
        });
    }
}
