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
