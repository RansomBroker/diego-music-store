<!-- STEP 1: CREDENTIALS FORM -->
<form wire:submit.prevent="login" class="space-y-6">
    <!-- Email Field -->
    <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Username atau Alamat Email</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                <i class="ph-bold ph-envelope text-lg"></i>
            </span>
            <input 
                wire:model.defer="email" 
                type="text" 
                id="email" 
                placeholder="Username atau email" 
                class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 focus:border-primary dark:focus:border-blue-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-primary-light rounded-2xl outline-none transition-all text-sm font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400"
                required
            >
        </div>
        @error('email')
            <p class="text-rose-500 text-xs font-medium mt-1.5 flex items-center gap-1">
                <i class="ph-bold ph-warning-circle"></i>
                <span>{{ $message }}</span>
            </p>
        @enderror
    </div>

    <!-- Password Field -->
    <div>
        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-500 dark:text-slate-400 mb-2">Kata Sandi</label>
        <div class="relative">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-slate-400">
                <i class="ph-bold ph-lock text-lg"></i>
            </span>
            <input 
                wire:model.defer="password" 
                type="password" 
                id="password" 
                placeholder="••••••••" 
                class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 focus:border-primary dark:focus:border-blue-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-primary-light rounded-2xl outline-none transition-all text-sm font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400"
                required
            >
        </div>
        @error('password')
            <p class="text-rose-500 text-xs font-medium mt-1.5 flex items-center gap-1">
                <i class="ph-bold ph-warning-circle"></i>
                <span>{{ $message }}</span>
            </p>
        @enderror
    </div>

    <!-- Remember Me -->
    <div class="flex items-center">
        <label class="flex items-center gap-2.5 cursor-pointer select-none">
            <input 
                wire:model.defer="remember" 
                type="checkbox" 
                class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primary-light cursor-pointer"
            >
            <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Ingat Saya</span>
        </label>
    </div>

    <!-- Submit Button -->
    <x-pos.utility.button 
        type="submit" 
        variant="primary"
        class="w-full justify-center !py-3.5 !rounded-2xl"
        icon="ph-sign-in"
    >
        Masuk Kasir
    </x-pos.utility.button>
</form>
