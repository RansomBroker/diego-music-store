<!-- STEP 2: SELECT BRANCH FORM -->
<form wire:submit.prevent="selectBranchAndCompleteLogin" class="space-y-6">
    <div>
        <label for="selectedBranchId" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Lokasi Cabang Aktif</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                <i class="ph-bold ph-storefront text-lg"></i>
            </span>
            <select 
                wire:model.defer="selectedBranchId" 
                id="selectedBranchId" 
                class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 focus:border-primary dark:focus:border-blue-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-primary-light rounded-2xl outline-none transition-all text-sm font-medium text-slate-800 dark:text-slate-100"
            >
                @foreach ($availableBranches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }} {{ $b->city ? '('.$b->city.')' : '' }}</option>
                @endforeach
            </select>
        </div>
        @error('selectedBranchId')
            <p class="text-rose-500 text-xs font-medium mt-1.5 flex items-center gap-1">
                <i class="ph-bold ph-warning-circle"></i>
                <span>{{ $message }}</span>
            </p>
        @enderror
    </div>

    <div class="space-y-2">
        <button 
            type="submit" 
            class="w-full py-3.5 px-4 bg-primary hover:bg-primaryHover text-white font-semibold rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
        >
            <span wire:loading wire:target="selectBranchAndCompleteLogin" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <span>Konfirmasi & Masuk POS</span>
            <i wire:loading.remove wire:target="selectBranchAndCompleteLogin" class="ph-bold ph-check text-lg"></i>
        </button>

        <button 
            type="button" 
            wire:click="backToCredentials"
            class="w-full py-2.5 px-4 text-xs font-semibold text-slate-500 hover:text-slate-800 dark:hover:text-slate-200 transition-colors"
        >
            Batal & Kembali
        </button>
    </div>
</form>
