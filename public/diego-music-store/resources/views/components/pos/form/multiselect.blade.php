@props([
    'label' => null,
    'icon' => null,
    'model' => null,
    'options' => [], // array of objects, strings, or arrays
    'placeholder' => 'Pilih...',
    'size' => 'md',
    'rounded' => 'rounded-xl',
])

@php
    $wireModel = $attributes->wire('model');
    $modelName = $model ?? ($wireModel->directive() ? $wireModel->value() : null);

    $paddingClass = $size === 'sm' ? ($icon ? 'pl-9' : 'px-3.5') : ($icon ? 'pl-11' : 'px-3.5');
    $paddingY = $size === 'sm' ? 'py-2' : 'py-2.5';
    $textSize = $size === 'sm' ? 'text-xs' : 'text-sm';
    $iconClass = $size === 'sm' ? 'left-3 text-sm' : 'left-3.5 text-base';
    $rightPadding = $size === 'sm' ? 'pr-8' : 'pr-10';
    $caretClass = $size === 'sm' ? 'right-3 text-xs' : 'right-3.5 text-sm';
@endphp

<div>
    @if ($label)
        <label class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1.5">
            {{ $label }}
        </label>
    @endif

    <div class="relative" x-data="{ open: false, selected: @entangle($modelName).live }" @click.outside="open = false">
        @if ($icon)
            <i class="ph {{ $icon }} text-slate-400 dark:text-slate-500 absolute {{ $iconClass }} top-1/2 -translate-y-1/2 pointer-events-none z-10"></i>
        @endif

        <button
            type="button"
            @click="open = !open"
            class="relative w-full text-left {{ $paddingClass }} {{ $rightPadding }} {{ $paddingY }} bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 {{ $rounded }} outline-hidden focus:outline-hidden font-bold {{ $textSize }} focus:ring-2 focus:ring-primary dark:focus:ring-blue-500 text-slate-800 dark:text-slate-100 appearance-none cursor-pointer"
        >
            <span class="block truncate" x-text="(selected && selected.length > 0) ? selected.length + ' Terpilih' : '{{ $placeholder }}'" :class="(!selected || selected.length === 0) ? 'text-slate-400 font-normal' : ''"></span>
        </button>

        <i class="ph ph-caret-down text-slate-400 dark:text-slate-500 absolute {{ $caretClass }} top-1/2 -translate-y-1/2 pointer-events-none z-10"></i>

        <div
            x-show="open"
            x-transition
            x-cloak
            style="display: none;"
            class="absolute z-50 w-full mt-1 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-lg max-h-60 overflow-y-auto"
        >
            <div class="p-2 space-y-1">
                @if (empty($options) || (is_object($options) && $options->isEmpty()))
                    <div class="text-xs text-slate-500 text-center py-2">Tidak ada opsi</div>
                @else
                    @foreach($options as $val => $text)
                        @php
                            $value = is_object($text) ? ($text->id ?? $text->name) : (is_array($text) ? ($text['value'] ?? $val) : (is_numeric($val) ? $text : $val));
                            $display = is_object($text) ? $text->name : (is_array($text) ? ($text['label'] ?? $text['name'] ?? $val) : $text);
                        @endphp
                        <label class="flex items-center px-2 py-1.5 hover:bg-slate-50 dark:hover:bg-slate-700 rounded-lg cursor-pointer">
                            <input
                                type="checkbox"
                                value="{{ $value }}"
                                wire:model.live="{{ $modelName }}"
                                class="w-4 h-4 text-primary bg-slate-100 border-slate-300 rounded focus:ring-primary dark:focus:ring-primary dark:ring-offset-slate-800 focus:ring-2 dark:bg-slate-700 dark:border-slate-600"
                            >
                            <span class="ml-2 text-sm font-semibold text-slate-700 dark:text-slate-200">{{ $display }}</span>
                        </label>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
