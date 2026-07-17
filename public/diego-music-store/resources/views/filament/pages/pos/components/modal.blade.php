@props([
    'show',
    'title',
    'closeAction',
    'maxWidth' => 'md'
])

@php
    $maxWidthClass = match ($maxWidth) {
        'sm' => 'max-w-sm',
        'md' => 'max-w-md',
        'lg' => 'max-w-lg',
        'xl' => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        default => 'max-w-md'
    };
@endphp

@if ($show)
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-3xl w-full {{ $maxWidthClass }} shadow-2xl transition-all border border-slate-100 dark:border-slate-700 mx-4 relative">
            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
                <h3 class="text-lg font-bold text-slate-800 dark:text-slate-100">{{ $title }}</h3>
                <button wire:click="{{ $closeAction }}" class="w-8 h-8 rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-650 dark:hover:text-slate-200 flex items-center justify-center transition-colors">
                    <i class="ph-bold ph-x text-lg"></i>
                </button>
            </div>

            <!-- Modal Content -->
            <div class="p-6 max-h-[calc(100vh-160px)] overflow-y-auto no-scrollbar">
                {{ $slot }}
            </div>
        </div>
    </div>
@endif
