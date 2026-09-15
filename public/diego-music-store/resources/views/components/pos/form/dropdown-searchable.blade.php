@props([
    'label' => null,
    'required' => false,
    'searchModel' => 'product_search',
    'openModel' => 'showProductDropdown',
    'placeholder' => 'Ketik nama untuk mencari...',
    'items' => [],
    'selectedId' => null,
    'selectedName' => null,
    'selectAction' => 'selectProduct',
    'clearAction' => 'clearSelectedProduct',
    'errorField' => null,
    'selectedLabel' => 'Item terpilih',
    'changeLabel' => 'Ganti Pilihan',
    'icon' => 'ph-magnifying-glass',
    'minChars' => 1,
])

@php
    $errorKey = $errorField ?? $searchModel;
@endphp

<div {{ $attributes->merge(['class' => 'space-y-2']) }}>
    @if ($label)
        <label class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200">
            {{ $label }}
            @if ($required)
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif

    <div class="relative" x-data="{ open: @entangle($openModel) }" @click.outside="open = false">
        <!-- Search Input with Clear Button -->
        <div class="relative">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3.5 pointer-events-none text-slate-400 dark:text-slate-500">
                <i class="ph {{ $icon }} text-base"></i>
            </span>
            <input
                type="text"
                wire:model.live.debounce.250ms="{{ $searchModel }}"
                @focus="if ({{ (int) $minChars }} === 0 || $wire.{{ $searchModel }}?.length >= {{ (int) $minChars }}) open = true"
                @click="if ({{ (int) $minChars }} === 0 || $wire.{{ $searchModel }}?.length >= {{ (int) $minChars }}) open = true"
                @keydown.escape="open = false"
                placeholder="{{ $placeholder }}"
                class="w-full pl-10 pr-10 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl text-sm font-semibold text-slate-800 dark:text-slate-100 focus:outline-hidden focus:ring-2 focus:ring-primary"
            />
            @if ((int) $minChars === 0)
                <span
                    x-show="!$wire.{{ $searchModel }} || $wire.{{ $searchModel }}.length === 0"
                    class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 dark:text-slate-500"
                >
                    <i class="ph-bold ph-caret-down text-xs"></i>
                </span>
            @endif
            <button
                type="button"
                x-show="$wire.{{ $searchModel }}?.length > 0"
                wire:click="{{ $clearAction }}"
                class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 cursor-pointer"
                title="Hapus pencarian"
            >
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>

        <!-- Dropdown Menu -->
        <div
            x-show="open && ({{ (int) $minChars }} === 0 || ($wire.{{ $searchModel }} && $wire.{{ $searchModel }}.length >= {{ (int) $minChars }}))"
            x-cloak
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 translate-y-1"
            x-transition:enter-end="opacity-100 translate-y-0"
            class="absolute z-30 w-full mt-1.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-2xl max-h-56 overflow-y-auto divide-y divide-slate-100 dark:divide-slate-700/60"
            style="display: none;"
        >
            @if (!empty($items) && count($items) > 0)
                @foreach ($items as $item)
                    <button
                        type="button"
                        wire:click="{{ $selectAction }}({{ $item->id ?? $item['id'] }})"
                        @click="open = false"
                        class="w-full text-left px-4 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-700 text-xs sm:text-sm flex items-center justify-between transition cursor-pointer"
                    >
                        <div>
                            <div class="font-bold text-slate-800 dark:text-slate-100">
                                {{ $item->name ?? $item['name'] }}
                            </div>
                            @php
                                $itemSku = $item->sku ?? $item->default_variant?->sku ?? $item['sku'] ?? null;
                            @endphp
                            @if (!empty($itemSku))
                                <div class="text-[11px] text-slate-400 ">SKU: {{ $itemSku }}</div>
                            @elseif (!empty($item->phone ?? $item['phone'] ?? null))
                                <div class="text-[11px] text-slate-400">{{ $item->phone ?? $item['phone'] }}</div>
                            @elseif (!empty($item->code ?? $item['code'] ?? null))
                                <div class="text-[11px] text-slate-400 ">{{ $item->code ?? $item['code'] }}</div>
                            @endif
                        </div>
                        @php
                            $itemPrice = $item->default_variant?->price ?? $item->price ?? $item['price'] ?? null;
                        @endphp
                        @if ($itemPrice !== null && $itemPrice > 0)
                            <div class="text-xs text-primary dark:text-blue-400 font-semibold ">
                                Rp {{ number_format($itemPrice, 0, ',', '.') }}
                            </div>
                        @endif
                    </button>
                @endforeach
            @elseif ($slot->isNotEmpty())
                {{ $slot }}
            @else
                <div class="p-3.5 text-center text-xs text-slate-400 dark:text-slate-500">
                    Tidak ada hasil yang cocok.
                </div>
            @endif
        </div>
    </div>

    <!-- Selected Badge Confirmation -->
    @if ($selectedName && $selectedId)
        <div class="p-2.5 rounded-xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800/60 flex items-center justify-between text-xs">
            <div class="flex items-center gap-2 text-emerald-700 dark:text-emerald-300 font-semibold">
                <i class="ph-bold ph-check-circle text-base text-emerald-600"></i>
                <span>{{ $selectedLabel }}: <strong class="text-slate-900 dark:text-white font-bold">{{ $selectedName }}</strong></span>
            </div>
            <button
                type="button"
                wire:click="{{ $clearAction }}"
                class="text-[11px] text-rose-500 hover:text-rose-700 font-bold hover:underline cursor-pointer"
            >
                {{ $changeLabel }}
            </button>
        </div>
    @endif

    @if ($errorKey)
        @error($errorKey)
            <span class="text-xs text-rose-500 font-semibold">{{ $message }}</span>
        @enderror
    @endif
</div>
