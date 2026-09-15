@props([
    'label' => null,
    'labelClass' => null,
    'icon' => null,
    'model' => null,
    'required' => false,
    'live' => false,
    'placeholder' => null,
    'options' => null,
    'size' => 'md',
    'rounded' => 'rounded-xl',
])

@php
    $wireModel = $attributes->wire('model');
    $modelName = $model ?? ($wireModel->directive() ? $wireModel->value() : null);
    $isLive = $live || ($wireModel->directive() && str_contains($wireModel->directive(), 'live'));

    $paddingClass = $size === 'sm' ? ($icon ? 'pl-9' : 'px-3.5') : ($icon ? 'pl-11' : 'px-3.5');
    $paddingY = $size === 'sm' ? 'py-2' : 'py-2.5';
    $textSize = $size === 'sm' ? 'text-xs' : 'text-sm';
    $iconClass = $size === 'sm' ? 'left-3 text-sm' : 'left-3.5 text-base';
    $rightPadding = $size === 'sm' ? 'pr-8' : 'pr-10';
    $caretClass = $size === 'sm' ? 'right-3 text-xs' : 'right-3.5 text-sm';
    $finalLabelClass = $labelClass ?? 'block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5';
@endphp

<div>
    @if ($label)
        <label class="{{ $finalLabelClass }}">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative">
        @if ($icon)
            <i class="ph {{ $icon }} text-slate-400 dark:text-slate-500 absolute {{ $iconClass }} top-1/2 -translate-y-1/2 pointer-events-none"></i>
        @endif

        <select
            @if ($wireModel->directive())
                {{-- wire:model directive already provided via attributes --}}
            @elseif ($isLive)
                wire:model.live="{{ $modelName }}"
            @elseif ($modelName)
                wire:model="{{ $modelName }}"
            @endif
            {{ $attributes->merge([
                'class' => "w-full {$paddingClass} {$rightPadding} {$paddingY} bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 {$rounded} outline-hidden focus:outline-hidden font-medium {$textSize} focus:ring-2 focus:ring-primary dark:focus:ring-blue-500 text-slate-800 dark:text-slate-100 appearance-none cursor-pointer"
            ]) }}
            @if ($required) required @endif
        >
            @if ($placeholder)
                <option value="" class="bg-white dark:bg-slate-900 text-slate-400">{{ $placeholder }}</option>
            @endif

            @if (!empty($options))
                @foreach ($options as $val => $text)
                    @if (is_array($text))
                        <option value="{{ $text['value'] ?? $val }}" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                            {{ $text['label'] ?? $text['name'] ?? $val }}
                        </option>
                    @else
                        <option value="{{ $val }}" class="bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-100">
                            {{ $text }}
                        </option>
                    @endif
                @endforeach
            @endif

            {{ $slot }}
        </select>

        <i class="ph ph-caret-down text-slate-400 dark:text-slate-500 absolute {{ $caretClass }} top-1/2 -translate-y-1/2 pointer-events-none"></i>
    </div>

    @if ($modelName)
        @error($modelName)
            <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span>
        @enderror
    @endif
</div>
