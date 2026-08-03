@props([
    'value' => null,
])

@php
    $wireModel = $attributes->wire('model');
    $wireKeyup = $attributes->get('wire:keyup');
@endphp

<div 
    x-data="{
        @if($wireModel->directive())
            rawVal: @entangle($wireModel),
        @else
            rawVal: {{ json_encode($value ?? 0) }},
        @endif
        displayVal: '',
        init() {
            this.updateDisplay(this.rawVal);
            this.$watch('rawVal', (newVal) => {
                this.updateDisplay(newVal);
            });
        },
        updateDisplay(val) {
            if (val === null || val === undefined || val === '') {
                this.displayVal = '';
                return;
            }
            const digits = String(val).replace(/[^\d]/g, '');
            this.displayVal = digits ? new Intl.NumberFormat('id-ID').format(digits) : '';
        },
        onInput(e) {
            const inputEl = e.target;
            const start = inputEl.selectionStart;
            const oldLen = inputEl.value.length;

            const digits = inputEl.value.replace(/[^\d]/g, '');
            const numeric = digits ? parseInt(digits, 10) : 0;
            const formatted = digits ? new Intl.NumberFormat('id-ID').format(digits) : '';

            this.displayVal = formatted;
            this.rawVal = numeric;

            this.$nextTick(() => {
                const newLen = formatted.length;
                const newPos = Math.max(0, start + (newLen - oldLen));
                if (inputEl === document.activeElement) {
                    inputEl.setSelectionRange(newPos, newPos);
                }
            });

            @if($wireKeyup)
                $wire.call('{{ $wireKeyup }}');
            @endif
        }
    }"
    class="relative w-full"
>
    <input 
        type="text" 
        inputmode="numeric"
        x-model="displayVal"
        @input="onInput($event)"
        {{ $attributes->whereDoesntStartWith('wire:model')->whereDoesntStartWith('wire:keyup')->merge([
            'class' => 'w-full px-4 py-2.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl outline-none font-bold text-base focus:ring-2 focus:ring-primary-light dark:focus:ring-blue-955 text-slate-800 dark:text-slate-100',
            'placeholder' => '0'
        ]) }}
    />
</div>
