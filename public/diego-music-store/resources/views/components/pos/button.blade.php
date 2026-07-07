@props([
    'type' => 'button',
    'variant' => 'primary', // primary, secondary, danger, outline
    'size' => 'md', // sm, md, lg
    'icon' => null,
    'loading' => null, // wire:target for loading state
])

@php
    $baseClasses = 'rounded-2xl font-bold transition-all flex items-center justify-center gap-2 group cursor-pointer select-none disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variantClasses = match ($variant) {
        'primary' => 'bg-primary hover:bg-primaryHover text-white shadow-lg shadow-blue-500/20 active:scale-[0.98]',
        'danger' => 'bg-red-500 hover:bg-red-600 text-white shadow-lg shadow-red-500/20 active:scale-[0.98]',
        'secondary' => 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 active:scale-[0.98]',
        'outline' => 'border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-350 hover:bg-slate-50 dark:hover:bg-slate-800 active:scale-[0.98]',
        default => 'bg-primary hover:bg-primaryHover text-white'
    };

    $sizeClasses = match ($size) {
        'sm' => 'px-4 py-2 text-xs',
        'md' => 'px-5 py-3 text-sm',
        'lg' => 'px-6 py-4 text-base w-full',
        default => 'px-5 py-3.5 text-sm'
    };
@endphp

<button 
    type="{{ $type }}" 
    {{ $attributes->merge(['class' => "$baseClasses $variantClasses $sizeClasses"]) }}
    @if ($loading) wire:loading.attr="disabled" @endif
>
    @if ($loading)
        <i class="ph ph-circle-notch animate-spin {{ $size === 'lg' ? 'text-xl' : 'text-base' }}" wire:loading wire:target="{{ $loading }}"></i>
    @endif
    
    @if ($icon)
        <span @if ($loading) wire:loading.remove wire:target="{{ $loading }}" @endif>
            <i class="ph {{ $icon }} {{ $size === 'lg' ? 'text-xl' : 'text-base' }} group-hover:scale-110 transition-transform"></i>
        </span>
    @endif
    
    <span>{{ $slot }}</span>
</button>
