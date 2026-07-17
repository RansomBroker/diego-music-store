@props([
    'label' => null,
    'icon' => null,
    'model',
    'live' => false,
    'size' => 'md'
])

@php
    $paddingClass = $size === 'sm' ? ($icon ? 'pl-9' : 'px-3.5') : ($icon ? 'pl-11' : 'px-4');
    $paddingY = $size === 'sm' ? 'py-2' : 'py-2.5';
    $textSize = $size === 'sm' ? 'text-xs' : 'text-sm';
    $iconClass = $size === 'sm' ? 'left-3 text-sm' : 'left-4 text-lg';
    $rightPadding = $size === 'sm' ? 'pr-8' : 'pr-10';
    $caretClass = $size === 'sm' ? 'right-3 text-[10px]' : 'right-4 text-xs';
@endphp

<div>
    @if ($label)
        <label class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1.5">{{ $label }}</label>
    @endif
    <div class="relative">
        @if ($icon)
            <i class="ph {{ $icon }} text-slate-600 dark:text-slate-350 absolute {{ $iconClass }} top-1/2 -translate-y-1/2 pointer-events-none font-bold"></i>
        @endif
        <select 
            @if ($live)
                wire:model.live="{{ $model }}"
            @else
                wire:model="{{ $model }}"
            @endif
            {{ $attributes->merge(['class' => "w-full {$paddingClass} {$rightPadding} {$paddingY} bg-white dark:bg-slate-900 border border-slate-400 dark:border-slate-600 rounded-lg outline-none font-bold {$textSize} focus:ring-2 focus:ring-primary-light dark:focus:ring-blue-950 text-slate-950 dark:text-white appearance-none cursor-pointer"]) }}
        >
            {{ $slot }}
        </select>
        <i class="ph ph-caret-down text-slate-600 dark:text-slate-350 absolute {{ $caretClass }} top-1/2 -translate-y-1/2 pointer-events-none font-bold"></i>
    </div>
</div>
