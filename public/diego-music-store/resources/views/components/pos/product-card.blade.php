@props([
    'variant',
    'selectedBranchId' => null,
    'selectedPricingTierId' => null,
    'qtyInCart' => 0,
    'clickAction' => 'addVariant', // 'addVariant' or 'addToCart'
])

@php
    $product = $variant->product;
    $stock = $selectedBranchId ? $variant->stockForBranch($selectedBranchId) : $variant->totalStock();
    $isService = $product->isService();
    $isOutOfStock = !$isService && $stock <= 0;
    $emoji = $isService ? '🛠️' : ($product->isBundle() ? '📦' : '🎸');
    
    $nameLower = strtolower($product->name . ' ' . ($variant->name ?? ''));
    if (str_contains($nameLower, 'drum') || str_contains($nameLower, 'stick')) {
        $emoji = '🥁';
    } elseif (str_contains($nameLower, 'keyboard') || str_contains($nameLower, 'piano')) {
        $emoji = '🎹';
    } elseif (str_contains($nameLower, 'senar') || str_contains($nameLower, 'kabel') || str_contains($nameLower, 'jack')) {
        $emoji = '🔌';
    }

    $price = ($selectedPricingTierId && is_numeric($selectedPricingTierId)) ? $variant->priceForTier((int)$selectedPricingTierId) : $variant->price;
@endphp

<div 
    @if (!$isOutOfStock) wire:click="{{ $clickAction }}({{ $variant->id }})" @endif 
    class="p-3 rounded-2xl shadow-sm border transition-all flex flex-col justify-between {{ $isOutOfStock ? 'opacity-40 saturate-50 bg-slate-100/60 dark:bg-slate-800/40 border-slate-200 dark:border-slate-700 pointer-events-none cursor-not-allowed select-none' : ($qtyInCart > 0 ? 'bg-blue-50/30 dark:bg-blue-950/20 border-primary dark:border-blue-500 shadow-blue-500/5 cursor-pointer group' : 'bg-white dark:bg-slate-800 border-slate-200 dark:border-slate-700 hover:shadow-md hover:border-primary dark:hover:border-blue-500 cursor-pointer group') }}"
>
    <div>
        <div class="aspect-[4/3] w-full bg-slate-100 dark:bg-slate-700 rounded-xl mb-3 flex items-center justify-center text-4xl group-hover:scale-[1.02] transition-transform relative overflow-hidden">
            {{ $emoji }}
            @if ($qtyInCart > 0)
                <span class="absolute top-2 right-2 bg-primary text-white text-xs font-black w-6 h-6 rounded-full flex items-center justify-center shadow-md animate-scale-up">
                    {{ $qtyInCart }}
                </span>
            @endif
        </div>
        <div class="px-1">
            <h3 class="font-bold text-slate-900 dark:text-white text-sm mb-1 line-clamp-2">
                {{ $product->name }} 
                @if ($variant->name)
                    <span class="text-xs text-slate-700 dark:text-slate-300 font-semibold">({{ $variant->name }})</span>
                @endif
            </h3>
            
            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                @if ($isService)
                    <span class="text-[10px] bg-indigo-50 dark:bg-indigo-950/40 text-indigo-700 dark:text-indigo-300 px-2 py-0.5 rounded-md font-bold border border-indigo-200 dark:border-indigo-800">Jasa</span>
                @elseif ($product->isBundle())
                    <span class="text-[10px] bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-300 px-2 py-0.5 rounded-md font-bold border border-purple-200 dark:border-purple-800">Paket</span>
                    <span class="text-[10px] {{ $stock > 0 ? 'bg-slate-150 dark:bg-slate-700 text-slate-800 dark:text-slate-200' : 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' }} px-2 py-0.5 rounded-md font-bold">Stok: {{ $stock }}</span>
                @else
                    <span class="text-[10px] bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-300 px-2 py-0.5 rounded-md font-bold border border-blue-200 dark:border-blue-800">Fisik</span>
                    <span class="text-[10px] {{ $stock > 0 ? 'bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-300 border border-green-200 dark:border-green-800' : 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800' }} px-2 py-0.5 rounded-md font-bold">Stok: {{ $stock }}</span>
                @endif
            </div>
        </div>
    </div>
    
    <div class="px-1 mt-4 flex items-center justify-between gap-1 flex-wrap">
        <span class="font-bold text-primary dark:text-blue-400 text-sm whitespace-nowrap">Rp {{ number_format($price, 0, ',', '.') }}</span>
        
        @if ($isOutOfStock)
            <span class="text-xs font-black text-rose-600 dark:text-rose-400 bg-rose-50 dark:bg-rose-950/40 px-2.5 py-1.5 rounded-xl border border-rose-200/50 dark:border-rose-900/40">Habis</span>
        @elseif ($qtyInCart > 0 && $clickAction === 'addToCart')
            <div class="flex items-center gap-1.5 bg-slate-100 dark:bg-slate-900 border border-slate-250 dark:border-slate-700 rounded-lg p-0.5">
                <x-pos.utility.button 
                    wire:click.stop="updateQty({{ $variant->id }}, -1)"
                    class="!rounded-full !w-6 !h-6 !p-0 !bg-white hover:!bg-red-500 !text-slate-700 hover:!text-white dark:!bg-slate-700 dark:!text-slate-300 dark:hover:!bg-red-650 dark:hover:!text-white !shadow-none flex-shrink-0"
                >
                    <i class="ph-bold ph-minus text-[10px]"></i>
                </x-pos.utility.button>
                <span class="w-4 text-center text-xs font-black text-slate-800 dark:text-white">{{ $qtyInCart }}</span>
                <x-pos.utility.button 
                    wire:click.stop="updateQty({{ $variant->id }}, 1)"
                    class="!rounded-full !w-6 !h-6 !p-0 !bg-white hover:!bg-primary !text-slate-700 hover:!text-white dark:!bg-slate-700 dark:!text-slate-300 dark:hover:!bg-primary dark:hover:!text-white !shadow-none flex-shrink-0"
                >
                    <i class="ph-bold ph-plus text-[10px]"></i>
                </x-pos.utility.button>
            </div>
        @else
            <x-pos.utility.button 
                wire:click.stop="{{ $clickAction }}({{ $variant->id }})"
                class="!rounded-full !w-8 !h-8 !p-0 !bg-primary-light hover:!bg-primary !text-primary hover:!text-white dark:!bg-blue-950/60 dark:!text-blue-400 dark:hover:!bg-primary dark:hover:!text-white !shadow-none flex-shrink-0"
            >
                <i class="ph-bold ph-plus"></i>
            </x-pos.utility.button>
        @endif
    </div>
</div>
