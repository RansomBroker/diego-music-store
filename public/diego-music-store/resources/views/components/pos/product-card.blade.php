@props(['variant', 'selectedBranchId', 'selectedPricingTierId' => null])

@php
    $product = $variant->product;
    $stock = $variant->stockForBranch($selectedBranchId);
    $isService = $product->isService();
    $emoji = $isService ? '🛠️' : ($product->isBundle() ? '📦' : '🎸');
    
    $nameLower = strtolower($product->name . ' ' . $variant->name);
    if (str_contains($nameLower, 'drum') || str_contains($nameLower, 'stick')) {
        $emoji = '🥁';
    } elseif (str_contains($nameLower, 'keyboard') || str_contains($nameLower, 'piano')) {
        $emoji = '🎹';
    } elseif (str_contains($nameLower, 'senar') || str_contains($nameLower, 'kabel') || str_contains($nameLower, 'jack')) {
        $emoji = '🔌';
    }

    $price = ($selectedPricingTierId && is_numeric($selectedPricingTierId)) ? $variant->priceForTier((int)$selectedPricingTierId) : $variant->price;
@endphp

<div wire:click="addToCart({{ $variant->id }})" class="bg-white dark:bg-slate-800 p-3 rounded-2xl shadow-sm border border-slate-100 dark:border-slate-700 hover:shadow-md hover:border-primary-light dark:hover:border-slate-600 transition-all cursor-pointer group flex flex-col justify-between">
    <div>
        <div class="aspect-[4/3] w-full bg-slate-100 dark:bg-slate-700 rounded-xl mb-3 flex items-center justify-center text-4xl group-hover:scale-[1.02] transition-transform">
            {{ $emoji }}
        </div>
        <div class="px-1">
            <h3 class="font-semibold text-slate-800 dark:text-slate-200 text-sm mb-1 line-clamp-2">
                {{ $product->name }} 
                @if ($variant->name)
                    <span class="text-xs text-slate-500 dark:text-slate-400 font-normal">({{ $variant->name }})</span>
                @endif
            </h3>
            
            <div class="flex items-center gap-1.5 mt-1 flex-wrap">
                @if ($isService)
                    <span class="text-[10px] bg-indigo-50 dark:bg-indigo-950/40 text-indigo-650 dark:text-indigo-400 px-2 py-0.5 rounded-md font-semibold">Jasa</span>
                @elseif ($product->isBundle())
                    <span class="text-[10px] bg-purple-50 dark:bg-purple-950/40 text-purple-650 dark:text-purple-400 px-2 py-0.5 rounded-md font-semibold">Paket</span>
                    <span class="text-[10px] bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 px-2 py-0.5 rounded-md font-medium">Stok: {{ $stock }}</span>
                @else
                    <span class="text-[10px] bg-blue-50 dark:bg-blue-950/40 text-blue-650 dark:text-blue-400 px-2 py-0.5 rounded-md font-semibold">Fisik</span>
                    <span class="text-[10px] {{ $stock > 0 ? 'bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400' : 'bg-red-50 dark:bg-red-950/40 text-red-655 dark:text-red-400' }} px-2 py-0.5 rounded-md font-medium">Stok: {{ $stock }}</span>
                @endif
            </div>
        </div>
    </div>
    
    <div class="px-1 mt-4 flex items-center justify-between gap-1 flex-wrap">
        <span class="font-bold text-primary dark:text-blue-400 text-sm whitespace-nowrap">Rp {{ number_format($price, 0, ',', '.') }}</span>
        <x-pos.button 
            class="!rounded-full !w-8 !h-8 !p-0 !bg-primary-light hover:!bg-primary !text-primary hover:!text-white dark:!bg-blue-950/60 dark:!text-blue-400 dark:hover:!bg-primary dark:hover:!text-white !shadow-none flex-shrink-0"
        >
            <i class="ph-bold ph-plus"></i>
        </x-pos.button>
    </div>
</div>
