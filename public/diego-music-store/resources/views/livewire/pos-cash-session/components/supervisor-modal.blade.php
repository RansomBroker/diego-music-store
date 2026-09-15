<!-- ================= SUPERVISOR APPROVAL MODAL ================= -->
<div x-show="openModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/80 backdrop-blur-sm" x-cloak>
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 w-full max-w-md p-6 shadow-2xl space-y-6 text-left transition-colors">
        
        <div class="flex items-center gap-3.5 text-amber-600 dark:text-amber-500">
            <i class="ph-fill ph-warning-octagon text-3xl"></i>
            <div>
                <h3 class="text-lg font-bold text-slate-900 dark:text-white">Otorisasi Selisih Kasir</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">Persetujuan dari Owner / Admin dibutuhkan.</p>
            </div>
        </div>

        <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-250/25 dark:border-amber-900 p-4 rounded-xl text-xs text-amber-805 dark:text-amber-455 font-semibold space-y-1">
            <div>Ekspektasi Kasir: Rp {{ number_format($expectedCash, 0, ',', '.') }}</div>
            <div>Kas Fisik Diinput: Rp {{ number_format($actualCash, 0, ',', '.') }}</div>
            <div class="border-t border-amber-250/25 dark:border-amber-900 mt-2 pt-1 font-bold text-sm">
                Selisih: Rp {{ number_format($actualCash - $expectedCash, 0, ',', '.') }}
            </div>
        </div>

        <form wire:submit.prevent="authorizeAndClose" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Email Supervisor/Owner</label>
                <input type="email" wire:model="supervisorEmail" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent text-slate-800 dark:text-white transition-colors" placeholder="email@domain.com">
                @error('supervisorEmail') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">Password Supervisor/Owner</label>
                <input type="password" wire:model="supervisorPassword" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-2 focus:ring-primary focus:border-transparent text-slate-800 dark:text-white transition-colors" placeholder="••••••••">
                @error('supervisorPassword') <span class="text-xs text-rose-500 font-medium mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="flex gap-3 pt-2">
                <button type="button" wire:click="$set('showSupervisorModal', false)" class="flex-1 h-12 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 dark:hover:bg-slate-600 text-slate-650 dark:text-slate-200 font-bold rounded-xl text-sm transition-colors cursor-pointer">
                    Batal
                </button>
                <button type="submit" class="flex-1 h-12 bg-amber-600 hover:bg-amber-700 text-white font-bold rounded-xl text-sm shadow-md shadow-amber-600/10 transition-colors cursor-pointer">
                    Otorisasi & Tutup
                </button>
            </div>
        </form>
    </div>
</div>
