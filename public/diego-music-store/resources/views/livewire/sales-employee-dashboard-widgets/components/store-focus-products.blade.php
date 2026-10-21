<!-- 6b. Produk Fokus Toko (Back Office) -->
<div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-star text-purple-500 dark:text-purple-400"></i> Produk Fokus Bulan Ini (Back Office)
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Target prioritas penjualan cabang yang ditetapkan oleh Back Office</p>
        </div>
        @if(count($storeFocusProducts ?? []) > 0)
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/30 flex items-center gap-1 shadow-sm">
                <span class="w-1.5 h-1.5 rounded-full bg-purple-500 animate-pulse"></span>
                {{ count($storeFocusProducts) }} Item Aktif
            </span>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @forelse ($storeFocusProducts ?? [] as $item)
            <div class="p-3.5 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60 flex flex-col justify-between hover:border-purple-300 dark:hover:border-purple-600/50 hover:shadow-sm transition-all duration-200">
                <div class="space-y-1.5">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate" title="{{ $item['name'] }}">
                                {{ $item['name'] }}
                            </div>
                            <div class="flex items-center gap-1.5 text-[10px] text-slate-400 mt-0.5">
                                <span class="truncate">{{ $item['category'] }}</span>
                                <span>•</span>
                                <span class="font-mono text-[9px]">{{ $item['sku'] }}</span>
                            </div>
                        </div>
                        <span class="px-2 py-0.5 bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/40 rounded-md text-[9px] font-black uppercase flex-shrink-0 tracking-wider">
                            {{ $item['badge'] }}
                        </span>
                    </div>

                    @if(!empty($item['reason']))
                        <div class="text-[10px] text-slate-600 dark:text-slate-400 bg-slate-50 dark:bg-slate-800/80 p-1.5 rounded-md border border-slate-100 dark:border-slate-800 flex items-center gap-1">
                            <i class="ph-bold ph-info text-purple-500 flex-shrink-0"></i>
                            <span class="italic truncate">{{ $item['reason'] }}</span>
                        </div>
                    @endif
                </div>

                <div class="flex items-center justify-between mt-3 pt-2.5 border-t border-slate-100 dark:border-slate-800/80 text-xs">
                    <div>
                        <div class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wider">Harga Jual</div>
                        <div class="font-black text-slate-900 dark:text-white mt-0.5">
                            Rp {{ number_format($item['price'], 0, ',', '.') }}
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-[9px] text-slate-400 font-extrabold uppercase tracking-wider">Stok Toko</div>
                        <span class="inline-flex items-center gap-1 font-bold text-xs mt-0.5 {{ $item['stock'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500' }}">
                            <i class="ph-bold ph-package"></i> {{ $item['stock'] }} Unit
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-2 text-center text-xs text-slate-400 py-6 bg-white dark:bg-slate-900 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                <i class="ph-bold ph-star text-2xl text-slate-300 dark:text-slate-600 block mb-1"></i>
                Belum ada produk fokus aktif dari Back Office untuk cabang ini
            </div>
        @endforelse
    </div>
</div>
