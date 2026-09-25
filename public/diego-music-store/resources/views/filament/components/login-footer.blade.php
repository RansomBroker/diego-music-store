@if (request()->routeIs('filament.backoffice.auth.*') || request()->is('backoffice/login*') || ! auth()->check())
    <div class="w-full text-center py-6 text-xs text-slate-400 dark:text-slate-600">
        &copy; {{ date('Y') }} Diego Music Store & Repair. All rights reserved.
    </div>
@endif
