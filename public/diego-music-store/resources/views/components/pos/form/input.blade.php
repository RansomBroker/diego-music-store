@props([
    'label' => null,
    'type' => 'text',
    'placeholder' => '',
    'icon' => null,
    'model',
    'required' => false,
    'live' => false,
    'size' => 'md'
])

@php
    $paddingClass = $size === 'sm' ? ($icon ? 'pl-9' : 'px-3.5') : ($icon ? 'pl-11' : 'px-4');
    $paddingY = $size === 'sm' ? 'py-2' : 'py-2.5';
    $textSize = $size === 'sm' ? 'text-xs' : 'text-sm';
    $iconClass = $size === 'sm' ? 'left-3 text-sm' : 'left-4 text-lg';
@endphp

<div>
    @if ($label)
        <label class="block text-xs font-black text-slate-800 dark:text-slate-200 uppercase tracking-wider mb-1.5">{{ $label }}@if($required) *@endif</label>
    @endif
    <div class="relative">
        @if ($icon)
            <i class="ph {{ $icon }} text-slate-600 dark:text-slate-350 absolute {{ $iconClass }} top-1/2 -translate-y-1/2 font-bold"></i>
        @endif
        <input 
            type="{{ $type }}" 
            @if ($live)
                wire:model.live.debounce.300ms="{{ $model }}"
            @else
                wire:model="{{ $model }}"
            @endif
            {{ $attributes->merge(['class' => "w-full {$paddingClass} pr-4 {$paddingY} bg-white dark:bg-slate-900 border border-slate-400 dark:border-slate-600 rounded-lg outline-none font-bold {$textSize} focus:ring-2 focus:ring-primary-light dark:focus:ring-blue-950 text-slate-950 dark:text-white"]) }}
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
        >
    </div>
    @error($model)
        <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span>
    @enderror
</div>
