@props([
    'label' => null,
    'icon' => null,
    'model',
    'live' => false
])

<div>
    @if ($label)
        <label class="block text-xs font-bold text-slate-400 dark:text-slate-550 uppercase tracking-wider mb-1.5">{{ $label }}</label>
    @endif
    <div class="relative">
        @if ($icon)
            <i class="ph {{ $icon }} text-slate-400 dark:text-slate-550 absolute left-4 top-1/2 -translate-y-1/2 text-lg pointer-events-none"></i>
        @endif
        <select 
            @if ($live)
                wire:model.live="{{ $model }}"
            @else
                wire:model="{{ $model }}"
            @endif
            {{ $attributes->merge(['class' => 'w-full ' . ($icon ? 'pl-11' : 'px-4') . ' pr-10 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-700/60 rounded-2xl outline-none font-semibold text-sm focus:ring-2 focus:ring-primary-light dark:focus:ring-blue-950 text-slate-800 dark:text-slate-100 appearance-none cursor-pointer']) }}
        >
            {{ $slot }}
        </select>
        <i class="ph ph-caret-down text-slate-400 dark:text-slate-550 absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-xs"></i>
    </div>
</div>
