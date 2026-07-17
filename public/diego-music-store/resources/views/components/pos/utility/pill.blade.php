@props([
    'variant' => 'default',
    'size' => 'sm'
])

@php
    $baseClasses = 'inline-flex items-center justify-center font-bold transition-colors';

    // Sizes
    $sizes = [
        'xs' => 'px-2 py-0.5 text-[9px] rounded-md',
        'sm' => 'px-3 py-1 text-[11px] rounded-md',
        'md' => 'px-3.5 py-1.5 text-xs rounded-lg',
    ];

    // Variants
    $variants = [
        'default' => 'bg-slate-150 dark:bg-slate-700 text-slate-900 dark:text-slate-200 border border-slate-300 dark:border-slate-600 font-mono',
        'primary' => 'bg-blue-100 dark:bg-blue-950/60 text-blue-800 dark:text-blue-400 border border-blue-200 dark:border-blue-900',
        'warning' => 'bg-amber-100 dark:bg-amber-950/60 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-900/60 font-black uppercase tracking-wider',
        'danger' => 'bg-red-100 dark:bg-red-950/60 text-red-800 dark:text-red-400 border border-red-200 dark:border-red-900/60 font-black uppercase tracking-wider',
        'success' => 'bg-emerald-100 dark:bg-emerald-950/60 text-emerald-800 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/60 font-black uppercase tracking-wider',
    ];

    $classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['sm']) . ' ' . ($variants[$variant] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
