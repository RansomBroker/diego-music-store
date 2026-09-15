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
        <x-pos.utility.button 
            type="submit" 
            variant="primary"
            class="w-full justify-center !py-3.5 !rounded-2xl"
            icon="ph-check"
        >
            Konfirmasi & Masuk POS
        </x-pos.utility.button>

        <x-pos.utility.button 
            type="button" 
            variant="secondary"
            class="w-full justify-center !py-2.5 !rounded-xl"
            wire:click="backToCredentials"
        >
            Batal & Kembali
        </x-pos.utility.button>
    </div>
</form>
