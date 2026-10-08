@props([
    'label' => null,
    'labelClass' => null,
    'placeholder' => '',
    'model' => null,
    'required' => false,
    'live' => false,
    'debounce' => '250ms',
    'rounded' => 'rounded-xl',
    'rows' => 3,
])

@php
    $wireModel = $attributes->wire('model');
    $modelName = $model ?? ($wireModel->directive() ? $wireModel->value() : null);
    $isLive = $live || ($wireModel->directive() && str_contains($wireModel->directive(), 'live'));

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
        <textarea
            @if ($wireModel->directive())
                {{-- wire:model directive already provided via attributes --}}
            @elseif ($isLive)
                @if ($debounce)
                    wire:model.live.debounce.{{ $debounce }}="{{ $modelName }}"
                @else
                    wire:model.live="{{ $modelName }}"
                @endif
            @elseif ($modelName)
                wire:model="{{ $modelName }}"
            @endif
            {{ $attributes->merge(['class' => "w-full px-4 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 {$rounded} outline-hidden focus:outline-hidden font-medium text-sm focus:ring-2 focus:ring-primary dark:focus:ring-blue-500 text-slate-800 dark:text-slate-100"]) }}
            placeholder="{{ $placeholder }}"
            rows="{{ $rows }}"
            @if ($required) required @endif
        ></textarea>
    </div>
    @if ($modelName)
        @error($modelName)
            <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span>
        @enderror
    @endif
</div>
