<!-- 6. Produk Fokus Bulan Ini -->
<div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-star text-purple-500 dark:text-purple-400"></i> Produk Fokus Bulan Ini
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Produk prioritas dengan insentif komisi ekstra</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @forelse ($focusProducts as $item)
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between">
                <div>
                    <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate max-w-[130px]">{{ $item['name'] }}</div>
                    <div class="text-[10px] text-slate-400">{{ $item['category'] }}</div>
                    <div class="text-xs font-mono font-black text-primary dark:text-blue-400 mt-1">
                        Rp {{ number_format($item['price'], 0, ',', '.') }}
                    </div>
                </div>
                <span class="px-2 py-1 bg-purple-100 dark:bg-purple-500/20 text-purple-700 dark:text-purple-300 border border-purple-200 dark:border-purple-500/40 rounded-lg text-[9px] font-black uppercase text-center">
                    {{ $item['incentive'] }}
                </span>
            </div>
        @empty
            <div class="col-span-2 text-center text-xs text-slate-400 py-4">Belum ada produk fokus terdaftar</div>
        @endforelse
    </div>
</div>
