@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'disabled' => false
])

@php
    $baseClasses = 'relative inline-flex items-center justify-center gap-1.5 font-bold uppercase tracking-wider transition-all duration-200 cursor-pointer select-none rounded-lg border';
    
    // Sizes
    $sizes = [
        'sm' => 'px-3.5 py-2 text-xs',
        'md' => 'px-4.5 py-3 text-sm',
        'lg' => 'w-full py-2.5 text-sm rounded-xl font-black shadow-md',
    ];

    // Variants
    $variants = [
        'primary' => 'bg-primary hover:bg-primaryHover text-white border-transparent shadow-sm shadow-blue-500/20 active:scale-[0.98]',
        
        'secondary' => 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border-slate-350 dark:border-slate-650 active:scale-[0.98]',
        
        'danger' => 'bg-slate-100 dark:bg-slate-700 hover:bg-red-50 dark:hover:bg-red-950/20 text-slate-700 dark:text-slate-355 hover:text-red-600 dark:hover:text-red-400 border-slate-400/80 dark:border-slate-650 active:scale-[0.98]',
        
        'warning' => 'bg-slate-100 dark:bg-slate-700 hover:bg-amber-50 dark:hover:bg-amber-950/20 text-slate-700 dark:text-slate-355 hover:text-amber-600 dark:hover:text-amber-400 border-slate-400/80 dark:border-slate-650 active:scale-[0.98]',
        
        'info' => 'bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/20 text-slate-700 dark:text-slate-355 hover:text-blue-600 dark:hover:text-blue-400 border-slate-400/80 dark:border-slate-650 active:scale-[0.98]',
        
        'success' => 'bg-slate-100 dark:bg-slate-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 text-slate-700 dark:text-slate-355 hover:text-emerald-600 dark:hover:text-emerald-400 border-slate-400/80 dark:border-slate-650 active:scale-[0.98]',
    ];

    $classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);

    if ($disabled) {
        $classes .= ' opacity-40 cursor-not-allowed pointer-events-none';
    }
@endphp

<button 
    type="{{ $type }}"
    {{ $disabled ? 'disabled' : '' }}
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if ($icon)
        <i class="ph {{ $icon }} {{ $size === 'lg' ? 'text-xl group-hover:scale-110 transition-transform' : 'text-sm' }} font-bold"></i>
    @endif
    <span>{{ $slot }}</span>
</button>
