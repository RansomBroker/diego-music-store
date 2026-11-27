@php
    $variant = $variantId ? \App\Models\ProductVariant::with('product')->find($variantId) : null;
    $newPrice = intval($newPrice ?? 0);
@endphp

@if ($variant)
    <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-200/70 dark:border-gray-700/70 mb-2">
        <h4 class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">Varian Produk</h4>
        <p class="text-base font-bold text-gray-800 dark:text-gray-100 mt-0.5">
            [{{ $variant->sku }}] {{ $variant->product->name }}{{ $variant->name ? ' - ' . $variant->name : '' }}
        </p>
        <div class="grid grid-cols-2 gap-4 mt-3 pt-3 border-t border-gray-200/60 dark:border-gray-700/60">
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400 block">Harga Beli Saat Ini (Master)</span>
                <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                    {{ \App\Helpers\FormatHelper::rupiah($variant->cost_price ?? 0) }}
                </span>
            </div>
            <div>
                <span class="text-xs text-gray-500 dark:text-gray-400 block">Harga Beli Baru (Transaksi)</span>
                <span class="text-sm font-semibold text-primary-600 dark:text-primary-400">
                    {{ \App\Helpers\FormatHelper::rupiah($newPrice) }}
                </span>
            </div>
        </div>
    </div>
@else
    <div class="p-4 text-center text-sm text-gray-500 dark:text-gray-400">
        Silakan pilih produk/varian terlebih dahulu untuk melihat dan memperbarui harga Pricing Tiers.
    </div>
@endif
