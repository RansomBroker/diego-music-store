@props([
    'label',
    'sublabel' => null,
    'model'
])

<div class="flex items-center justify-between p-4 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-100 dark:border-slate-800">
    <div class="flex flex-col">
        <span class="text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $label }}</span>
        @if ($sublabel)
            <span class="text-[10px] text-slate-500 dark:text-slate-400">{{ $sublabel }}</span>
        @endif
    </div>
    <label class="relative inline-flex items-center cursor-pointer select-none">
        <input 
            type="checkbox" 
            wire:model="{{ $model }}" 
            class="sr-only peer"
        >
        <div class="w-11 h-6 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-primary dark:peer-checked:bg-blue-500"></div>
    </label>
</div>
