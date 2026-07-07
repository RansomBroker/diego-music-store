@props([
    'id',
    'item',
    'pricingTiers' => []
])

@php
    $itemVariant = \App\Models\ProductVariant::find($id);
@endphp

<div class="flex flex-col gap-2.5 pb-4 border-b border-slate-100 dark:border-slate-700/50 last:border-b-0 last:pb-0">
    <div class="flex items-center gap-3">
        <div class="w-14 h-14 bg-slate-100 dark:bg-slate-700 rounded-xl flex items-center justify-center text-xl flex-shrink-0">
            {{ $item['emoji'] }}
        </div>
        <div class="flex-1 min-w-0">
            <h4 class="font-semibold text-sm text-slate-800 dark:text-slate-200 truncate">{{ $item['name'] }}</h4>
            <div class="flex items-center gap-2">
                <span class="font-bold text-primary dark:text-blue-400 text-sm">{{ \App\Helpers\FormatHelper::rupiah($item['price']) }}</span>
                @if (intval($item['discount_amount'] ?? 0) > 0)
                    <span class="text-[10px] text-rose-500 font-semibold bg-rose-50 dark:bg-rose-950/30 px-1.5 py-0.5 rounded">
                        - {{ \App\Helpers\FormatHelper::rupiah($item['discount_amount']) }}
                    </span>
                @endif
            </div>
        </div>
        <!-- Counter -->
        <div class="flex items-center gap-2 bg-slate-50 dark:bg-slate-700 border border-slate-100 dark:border-slate-600 rounded-lg p-1">
            <button wire:click="updateQty({{ $id }}, -1)" class="w-7 h-7 rounded bg-white dark:bg-slate-600 shadow-sm flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i class="ph ph-minus"></i>
            </button>
            <span class="w-4 text-center text-sm font-semibold text-slate-800 dark:text-slate-200">{{ $item['qty'] }}</span>
            <button wire:click="updateQty({{ $id }}, 1)" class="w-7 h-7 rounded bg-white dark:bg-slate-600 shadow-sm flex items-center justify-center text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 transition-colors">
                <i class="ph ph-plus"></i>
            </button>
        </div>
    </div>

    <!-- Item Details Grid (Tingkat Harga, Diskon, Catatan) -->
    <div class="space-y-2.5 pl-[68px]">
        <!-- Row 1: Tingkat Harga & Diskon -->
        <div class="flex gap-3">
            <!-- Pricing Tier Selector Container -->
            <div class="flex-1">
                <label class="text-[9px] font-bold text-slate-400 dark:text-slate-550 uppercase tracking-wider block mb-1">Tingkat Harga</label>
                <div class="relative flex items-center bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200/50 dark:border-slate-700/60 overflow-hidden h-8">
                    <i class="ph ph-tag-chevron text-slate-400 dark:text-slate-500 text-xs absolute left-2 pointer-events-none"></i>
                    <select 
                        onchange="@this.call('updateItemPricingTier', {{ $id }}, this.value)"
                        class="w-full pl-7 pr-6 py-0 h-full bg-transparent border-none text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none focus:ring-0 cursor-pointer appearance-none"
                    >
                        @foreach ($pricingTiers as $tier)
                            <option value="{{ $tier->id }}" {{ ($item['pricing_tier_id'] ?? '') == $tier->id ? 'selected' : '' }} class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">
                                {{ $tier->name }} ({{ \App\Helpers\FormatHelper::rupiah($itemVariant ? $itemVariant->priceForTier($tier->id) : 0) }})
                            </option>
                        @endforeach
                    </select>
                    <i class="ph ph-caret-down text-slate-400 dark:text-slate-500 absolute right-2 pointer-events-none text-[10px]"></i>
                </div>
            </div>

            <!-- Discount Input Container -->
            <div class="w-32 flex-shrink-0">
                <label class="text-[9px] font-bold text-slate-400 dark:text-slate-555 uppercase tracking-wider block mb-1">Diskon Item</label>
                <div class="relative flex items-center bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200/50 dark:border-slate-700/60 overflow-hidden h-8">
                    <i class="ph ph-tag text-slate-400 dark:text-slate-500 text-xs absolute left-2 pointer-events-none"></i>
                    <input 
                        type="number" 
                        placeholder="{{ ($item['discount_type'] ?? 'fixed') === 'percent' ? '0 %' : 'Rp 0' }}" 
                        value="{{ ($item['discount_value'] ?? 0) > 0 ? $item['discount_value'] : '' }}"
                        onchange="@this.call('updateItemDiscountValue', {{ $id }}, this.value)"
                        class="w-full pl-7 pr-8 py-0 h-full bg-transparent border-none text-[11px] font-bold text-slate-700 dark:text-slate-300 outline-none focus:ring-0"
                        min="0"
                    >
                    <button 
                        type="button"
                        wire:click="toggleItemDiscountType({{ $id }})"
                        class="absolute right-0 top-0 bottom-0 px-2 bg-slate-100 dark:bg-slate-800 text-[10px] font-black border-l border-slate-200/50 dark:border-slate-700 text-primary dark:text-blue-400 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors cursor-pointer flex items-center justify-center select-none"
                        title="Klik untuk mengubah jenis diskon (Nominal / Persentase)"
                    >
                        {{ ($item['discount_type'] ?? 'fixed') === 'percent' ? '%' : 'Rp' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Row 2: Catatan -->
        <div>
            <label class="text-[9px] font-bold text-slate-400 dark:text-slate-555 uppercase tracking-wider block mb-1">Catatan</label>
            <div class="relative flex items-center bg-slate-50 dark:bg-slate-900/50 rounded-lg border border-slate-200/50 dark:border-slate-700/60 overflow-hidden h-8">
                <i class="ph ph-note-pencil text-slate-400 dark:text-slate-500 text-xs absolute left-2 pointer-events-none"></i>
                <input 
                    type="text" 
                    placeholder="Tambahkan catatan..." 
                    value="{{ $item['notes'] ?? '' }}"
                    onchange="@this.call('updateItemNote', {{ $id }}, this.value)"
                    class="w-full pl-7 pr-2 py-0 h-full bg-transparent border-none text-[11px] font-medium text-slate-700 dark:text-slate-300 outline-none focus:ring-0"
                >
            </div>
        </div>
    </div>
</div>
