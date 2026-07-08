@php
    $variant = \App\Models\ProductVariant::with('product')->find($variantId);
    $tiers = \App\Models\PricingTier::all();
    $newPrice = intval($newPrice ?? 0);
@endphp

@if ($variant)
    <div class="space-y-4">
        <!-- Variant info summary -->
        <div class="p-4 bg-gray-50 dark:bg-gray-800/50 rounded-xl border border-gray-100 dark:border-gray-700/50">
            <h4 class="text-sm font-semibold text-gray-500 dark:text-gray-400">Varian Produk</h4>
            <p class="text-lg font-bold text-gray-800 dark:text-gray-200">
                [{{ $variant->sku }}] {{ $variant->product->name }}{{ $variant->name ? ' - ' . $variant->name : '' }}
            </p>
            <div class="grid grid-cols-2 gap-4 mt-3 pt-3 border-t border-gray-200/50 dark:border-gray-700/50">
                <div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 block">Harga Beli Saat Ini (Master)</span>
                    <span class="text-md font-semibold text-gray-700 dark:text-gray-300">
                        {{ \App\Helpers\FormatHelper::rupiah($variant->cost_price ?? 0) }}
                    </span>
                </div>
                <div>
                    <span class="text-xs text-gray-400 dark:text-gray-500 block">Harga Beli Baru (Transaksi)</span>
                    <span class="text-md font-semibold text-primary-600 dark:text-primary-400">
                        {{ \App\Helpers\FormatHelper::rupiah($newPrice) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Pricing Tiers Comparison Table -->
        <div class="overflow-x-auto rounded-xl border border-gray-200 dark:border-gray-700">
            <table class="w-full text-sm text-left text-gray-500 dark:text-gray-400">
                <thead class="text-xs text-gray-700 uppercase bg-gray-100 dark:bg-gray-800 dark:text-gray-300">
                    <tr>
                        <th scope="col" class="px-4 py-3">Pricing Tier</th>
                        <th scope="col" class="px-4 py-3">Tipe Tier</th>
                        <th scope="col" class="px-4 py-3 text-right">Harga Saat Ini</th>
                        <th scope="col" class="px-4 py-3 text-right">Estimasi Harga Baru</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($tiers as $tier)
                        @php
                            $currentTierPrice = $variant->priceForTier($tier->id);
                            
                            if ($tier->price_follows_hpp) {
                                // Follows HPP means new tier price will be the new buying price
                                $estimatedPrice = $newPrice;
                                $isUpdated = ($estimatedPrice !== $currentTierPrice);
                            } else {
                                $estimatedPrice = $currentTierPrice;
                                $isUpdated = false;
                            }
                        @endphp
                        <tr class="bg-white dark:bg-gray-900 hover:bg-gray-50 dark:hover:bg-gray-800/80 transition-colors">
                            <td class="px-4 py-3 font-medium text-gray-900 dark:text-gray-100">
                                {{ $tier->name }}
                            </td>
                            <td class="px-4 py-3">
                                @if ($tier->price_follows_hpp)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400">
                                        Mengikuti HPP
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-400">
                                        Harga Tetap (Manual)
                                    </span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right font-mono">
                                {{ \App\Helpers\FormatHelper::rupiah($currentTierPrice) }}
                            </td>
                            <td class="px-4 py-3 text-right font-semibold font-mono">
                                @if ($isUpdated)
                                    <div class="flex items-center justify-end gap-1.5">
                                        <span class="text-xs line-through text-gray-400">
                                            {{ \App\Helpers\FormatHelper::rupiah($currentTierPrice) }}
                                        </span>
                                        <span class="text-green-600 dark:text-green-400">
                                            {{ \App\Helpers\FormatHelper::rupiah($estimatedPrice) }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-gray-600 dark:text-gray-400">
                                        {{ \App\Helpers\FormatHelper::rupiah($estimatedPrice) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        
        <p class="text-xs text-gray-400 dark:text-gray-500 italic mt-1">
            * Estimasi harga baru hanya akan diterapkan jika Anda mengaktifkan checkbox <strong>"Update Master"</strong> pada baris barang ini saat transaksi diposting.
        </p>
    </div>
@else
    <div class="p-4 text-center text-gray-500 dark:text-gray-400">
        Silakan pilih produk/varian terlebih dahulu untuk melihat perbandingan harga.
    </div>
@endif
