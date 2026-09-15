<!-- Right Column: Live Store Badge Card Preview -->
<div class="space-y-6">
    <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl p-6 shadow-xl border border-slate-700/50 space-y-4 relative overflow-hidden">
        <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary/20 rounded-full blur-2xl pointer-events-none"></div>

        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-2xl overflow-hidden bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center flex-shrink-0 shadow-lg">
                @if ($logo)
                    <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                @elseif ($currentLogoUrl)
                    <img src="{{ $currentLogoUrl }}" class="w-full h-full object-cover">
                @else
                    <i class="ph-bold ph-storefront text-3xl text-primary-light"></i>
                @endif
            </div>
            <div>
                <span class="text-[10px] font-black tracking-widest text-primary-light uppercase">Official Store Badge</span>
                <h3 class="text-lg font-black leading-tight">{{ $store_name ?: 'Nama Toko' }}</h3>
                <p class="text-xs text-slate-300 font-medium">{{ $name ?: 'Nama Cabang' }}</p>
            </div>
        </div>

        <div class="border-t border-white/10 pt-3 space-y-2 text-xs text-slate-300">
            <div class="flex items-start gap-2">
                <i class="ph-fill ph-map-pin text-primary-light text-sm mt-0.5"></i>
                <span>{{ $address ?: 'Alamat belum diatur' }}</span>
            </div>
            <div class="flex items-center gap-2">
                <i class="ph-fill ph-phone text-primary-light text-sm"></i>
                <span>{{ $phone ?: 'No. Telp belum diatur' }}</span>
            </div>
        </div>

        <div class="pt-2 flex items-center justify-between text-[11px] font-semibold text-slate-400 border-t border-white/10">
            <span>Status POS</span>
            @if ($is_active)
                <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">OPERASIONAL</span>
            @else
                <span class="px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">NON-AKTIF</span>
            @endif
        </div>
    </div>
</div>
