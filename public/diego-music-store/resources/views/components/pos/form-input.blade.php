@props([
    'label',
    'type' => 'text',
    'placeholder' => '',
    'icon' => null,
    'model',
    'required' => false
])

<div>
    <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-1.5">{{ $label }}@if($required) *@endif</label>
    <div class="relative">
        @if ($icon)
            <i class="ph {{ $icon }} text-slate-400 dark:text-slate-500 absolute left-4 top-1/2 -translate-y-1/2 text-lg"></i>
        @endif
        <input 
            type="{{ $type }}" 
            wire:model="{{ $model }}" 
            class="w-full {{ $icon ? 'pl-11' : 'px-4' }} pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200/50 dark:border-slate-700/60 rounded-2xl outline-none font-semibold text-sm focus:ring-2 focus:ring-primary-light dark:focus:ring-blue-950 text-slate-800 dark:text-slate-100" 
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
        >
    </div>
    @error($model)
        <span class="text-xs text-red-500 font-semibold mt-1 block">{{ $message }}</span>
    @enderror
</div>
