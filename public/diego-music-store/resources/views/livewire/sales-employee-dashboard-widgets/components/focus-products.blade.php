<!-- 6. Produk Berinsentif Komisi Ekstra -->
<div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80">
    <div class="flex items-center justify-between mb-4">
        <div>
            <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
                <i class="ph-bold ph-coins text-amber-500 dark:text-amber-400"></i> Produk Berinsentif Komisi
            </h3>
            <p class="text-[11px] text-slate-500 dark:text-slate-400">Reward komisi tambahan dari skema komisi khusus produk/kategori</p>
        </div>
        @if(count($focusProducts ?? []) > 0)
            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-extrabold bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">
                {{ count($focusProducts) }} Skema Aktif
            </span>
        @endif
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
        @forelse ($focusProducts ?? [] as $item)
            <div class="p-3 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60 flex items-center justify-between hover:border-amber-300 dark:hover:border-amber-500/40 transition">
                <div class="min-w-0 pr-2">
                    <div class="text-xs font-bold text-slate-800 dark:text-slate-100 truncate" title="{{ $item['name'] }}">{{ $item['name'] }}</div>
                    <div class="text-[10px] text-slate-400 mt-0.5">{{ $item['category'] }}</div>
                    <div class="text-xs font-black text-amber-600 dark:text-amber-400 mt-1">
                        Rp {{ number_format($item['price'], 0, ',', '.') }}
                    </div>
                </div>
                <span class="px-2 py-1 bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-500/40 rounded-lg text-[9px] font-black uppercase text-center flex-shrink-0">
                    {{ $item['incentive'] }}
                </span>
            </div>
        @empty
            <div class="col-span-2 text-center text-xs text-slate-400 py-4 bg-white dark:bg-slate-900 rounded-xl border border-dashed border-slate-200 dark:border-slate-800">
                <i class="ph-bold ph-coins text-2xl text-slate-300 dark:text-slate-600 block mb-1"></i>
                Belum ada skema komisi khusus produk atau kategori aktif
            </div>
        @endforelse
    </div>
</div>
