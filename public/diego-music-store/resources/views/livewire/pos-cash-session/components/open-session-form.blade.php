<!-- ================= NO ACTIVE SESSION / OPEN SESSION FORM ================= -->
<div class="max-w-xl mx-auto bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 p-8 shadow-xl transition-colors">
    <div class="text-center mb-8">
        <div class="w-16 h-16 rounded-2xl bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center mx-auto mb-4">
            <i class="ph-bold ph-key text-3xl"></i>
        </div>
        <h2 class="text-xl font-bold text-slate-800 dark:text-white">Buka Sesi Kasir Baru</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Masukkan modal awal laci kasir Anda untuk memulai transaksi hari ini.</p>
    </div>

    <form wire:submit.prevent="openSession" class="space-y-6">
        <!-- Branch Select -->
        <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Pilih Cabang Bertugas</label>
            <div class="relative">
                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 dark:text-slate-500">
                    <i class="ph-bold ph-storefront text-lg"></i>
                </span>
                <select wire:model="selectedBranchId" class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent text-sm font-semibold text-slate-850 dark:text-slate-200 transition-colors">
                    <option value="">-- Pilih Cabang --</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->store_name ?: $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @error('selectedBranchId') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Opening Cash -->
        <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Modal Uang Tunai Awal (Opening Cash)</label>
            <x-money-input 
                wire:model.live="openingCash"
                class="w-full px-4 py-3.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent font-bold text-lg text-slate-850 dark:text-white transition-colors"
                placeholder="0"
            />
            @error('openingCash') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Quick presets -->
        <div class="flex flex-wrap gap-2">
            <button type="button" wire:click="selectOpeningPreset(200000)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors cursor-pointer">Rp 200.000</button>
            <button type="button" wire:click="selectOpeningPreset(500000)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors cursor-pointer">Rp 500.000</button>
            <button type="button" wire:click="selectOpeningPreset(1000000)" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-xs font-semibold rounded-lg transition-colors cursor-pointer">Rp 1.000.000</button>
        </div>

        <!-- Notes -->
        <div>
            <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Catatan Tambahan</label>
            <textarea wire:model="notes" rows="2" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl focus:ring-2 focus:ring-primary focus:border-transparent text-sm text-slate-800 dark:text-slate-200 transition-colors" placeholder="Catatan opsional pembukaan shift..."></textarea>
        </div>

        <!-- Submit -->
        <button type="submit" class="w-full flex items-center justify-center gap-2 h-13 bg-primary hover:bg-primaryDark text-white font-bold rounded-xl shadow-lg shadow-primary/20 transition-all cursor-pointer">
            <i class="ph-bold ph-keyhole text-lg"></i>
            <span>Buka Sesi & Mulai Transaksi</span>
        </button>
    </form>
</div>
