@props([
    'type' => 'button',
    'variant' => 'primary',
    'size' => 'md',
    'icon' => null,
    'disabled' => false,
    'href' => null,
    'target' => null,
])

@php
    $baseClasses = 'relative inline-flex items-center justify-center gap-1.5 font-bold uppercase tracking-wider transition-all duration-200 cursor-pointer select-none rounded-lg border';
    
    // Sizes
    $sizes = [
        'xs' => 'px-2.5 py-1 text-xs',
        'sm' => 'px-3.5 py-2 text-xs',
        'md' => 'px-4.5 py-3 text-sm',
        'lg' => 'w-full py-2.5 text-sm rounded-xl font-black shadow-md',
    ];

    // Variants (Semua varian aksi utama bertipe primary solid dengan warna yang disesuaikan)
    $variants = [
        'primary' => 'bg-primary hover:bg-primaryHover text-white border-transparent shadow-sm shadow-blue-500/20 active:scale-[0.98]',
        
        'secondary' => 'bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 border-slate-350 dark:border-slate-650 active:scale-[0.98]',
        
        'danger' => 'bg-rose-600 hover:bg-rose-700 dark:bg-rose-600 dark:hover:bg-rose-700 text-white border-transparent shadow-sm shadow-rose-600/20 active:scale-[0.98]',
        
        'warning' => 'bg-amber-500 hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700 text-white border-transparent shadow-sm shadow-amber-500/20 active:scale-[0.98]',
        
        'info' => 'bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-700 text-white border-transparent shadow-sm shadow-blue-600/20 active:scale-[0.98]',
        
        'success' => 'bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-700 text-white border-transparent shadow-sm shadow-emerald-600/20 active:scale-[0.98]',

        'soft-danger' => 'bg-slate-100 dark:bg-slate-700 hover:bg-red-50 dark:hover:bg-red-950/20 text-slate-700 dark:text-slate-200 hover:text-red-600 dark:hover:text-red-400 border-slate-400/80 dark:border-slate-650 active:scale-[0.98]',
        
        'soft-warning' => 'bg-slate-100 dark:bg-slate-700 hover:bg-amber-50 dark:hover:bg-amber-950/20 text-slate-700 dark:text-slate-200 hover:text-amber-600 dark:hover:text-amber-400 border-slate-400/80 dark:border-slate-650 active:scale-[0.98]',
        
        'soft-info' => 'bg-slate-100 dark:bg-slate-700 hover:bg-blue-50 dark:hover:bg-blue-950/20 text-slate-700 dark:text-slate-200 hover:text-blue-600 dark:hover:text-blue-400 border-slate-400/80 dark:border-slate-650 active:scale-[0.98]',
        
        'soft-success' => 'bg-slate-100 dark:bg-slate-700 hover:bg-emerald-50 dark:hover:bg-emerald-950/20 text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 border-slate-400/80 dark:border-slate-650 active:scale-[0.98]',
    ];

    $classes = $baseClasses . ' ' . ($sizes[$size] ?? $sizes['md']) . ' ' . ($variants[$variant] ?? $variants['primary']);

    if ($disabled) {
        $classes .= ' opacity-40 cursor-not-allowed pointer-events-none';
    }
@endphp

@if ($href)
    <a 
        href="{{ $href }}"
        @if ($target) target="{{ $target }}" @endif
        {{ $attributes->merge(['class' => $classes]) }}
    >
        @if ($icon)
            <i class="ph {{ $icon }} {{ $size === 'lg' ? 'text-xl group-hover:scale-110 transition-transform' : 'text-sm' }} font-bold"></i>
        @endif
        <span>{{ $slot }}</span>
    </a>
@else
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
@endif
