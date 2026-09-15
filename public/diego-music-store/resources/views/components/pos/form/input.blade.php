@props([
    'label' => null,
    'labelClass' => null,
    'type' => 'text',
    'placeholder' => '',
    'icon' => null,
    'prefix' => null,
    'suffix' => null,
    'model' => null,
    'required' => false,
    'live' => false,
    'debounce' => '250ms',
    'size' => 'md',
    'rounded' => 'rounded-xl',
    'currency' => false,
])

@php
    $wireModel = $attributes->wire('model');
    $modelName = $model ?? ($wireModel->directive() ? $wireModel->value() : null);
    $isLive = $live || ($wireModel->directive() && str_contains($wireModel->directive(), 'live'));
    $isCurrency = $currency || $type === 'currency';

    if ($isCurrency && $prefix === null) {
        $prefix = 'Rp';
    }

    $paddingClass = $size === 'sm'
        ? ($icon ? 'pl-9' : ($prefix ? 'pl-9' : 'px-3.5'))
        : ($icon ? 'pl-11' : ($prefix ? 'pl-11' : 'px-3.5'));
    $paddingRight = $suffix ? ($size === 'sm' ? 'pr-9' : 'pr-11') : 'pr-3.5';
    $paddingY = $size === 'sm' ? 'py-2' : 'py-2.5';
    $textSize = $size === 'sm' ? 'text-xs' : 'text-sm';
    $iconClass = $size === 'sm' ? 'left-3 text-sm' : 'left-3.5 text-base';
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
    <div 
        @if ($isCurrency)
            x-data="{
                rawVal: @if($wireModel->directive()) @entangle($wireModel){{ $isLive && !str_contains($wireModel->directive(), 'live') ? '.live' : '' }} @elseif($modelName) @entangle($modelName){{ $isLive ? '.live' : '' }} @else 0 @endif,
                displayVal: '',
                init() {
                    this.updateDisplay(this.rawVal);
                    this.$watch('rawVal', (newVal) => {
                        const inputEl = this.$refs.currencyInput;
                        if (inputEl && document.activeElement === inputEl && inputEl.value === '') {
                            return;
                        }
                        this.updateDisplay(newVal);
                    });
                },
                updateDisplay(val) {
                    if (val === null || val === undefined || val === '') {
                        this.displayVal = '';
                        return;
                    }
                    const cleanVal = typeof val === 'number' ? Math.round(val) : String(val).split('.')[0];
                    const digits = String(cleanVal).replace(/[^\d]/g, '');
                    if (!digits) {
                        this.displayVal = '';
                        return;
                    }
                    const num = parseInt(digits, 10);
                    this.displayVal = new Intl.NumberFormat('id-ID').format(num);
                },
                onInput(e) {
                    const inputEl = e.target;
                    const start = inputEl.selectionStart;
                    const oldLen = inputEl.value.length;

                    const digits = inputEl.value.replace(/[^\d]/g, '');
                    const numeric = digits ? parseInt(digits, 10) : 0;
                    const formatted = digits ? new Intl.NumberFormat('id-ID').format(numeric) : '';

                    this.displayVal = formatted;
                    this.rawVal = numeric;

                    this.$nextTick(() => {
                        const newLen = formatted.length;
                        const newPos = Math.max(0, start + (newLen - oldLen));
                        if (inputEl === document.activeElement) {
                            inputEl.setSelectionRange(newPos, newPos);
                        }
                    });
                }
            }"
        @endif
        class="relative"
    >
        @if ($icon)
            <i class="ph {{ $icon }} text-slate-400 dark:text-slate-500 absolute {{ $iconClass }} top-1/2 -translate-y-1/2 pointer-events-none"></i>
        @elseif ($prefix)
            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 dark:text-slate-500 pointer-events-none">{{ $prefix }}</span>
        @endif

        @if ($isCurrency)
            <input
                x-ref="currencyInput"
                type="text"
                inputmode="numeric"
                x-model="displayVal"
                @input="onInput($event)"
                placeholder="{{ $placeholder ?: '0' }}"
                {{ $attributes->whereDoesntStartWith('wire:model')->merge([
                    'class' => "w-full {$paddingClass} {$paddingRight} {$paddingY} bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 {$rounded} outline-hidden focus:outline-hidden font-medium font-mono {$textSize} focus:ring-2 focus:ring-primary dark:focus:ring-blue-500 text-slate-800 dark:text-slate-100"
                ]) }}
                @if ($required) required @endif
            />
        @else
            <input
                type="{{ $type }}"
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
                {{ $attributes->merge(['class' => "w-full {$paddingClass} {$paddingRight} {$paddingY} bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 {$rounded} outline-hidden focus:outline-hidden font-medium {$textSize} focus:ring-2 focus:ring-primary dark:focus:ring-blue-500 text-slate-800 dark:text-slate-100"]) }}
                placeholder="{{ $placeholder }}"
                @if ($required) required @endif
            />
        @endif

        @if ($suffix)
            <span class="absolute right-3.5 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400 dark:text-slate-500 pointer-events-none">{{ $suffix }}</span>
        @endif
    </div>
    @if ($modelName)
        @error($modelName)
            <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span>
        @enderror
    @endif
</div>
