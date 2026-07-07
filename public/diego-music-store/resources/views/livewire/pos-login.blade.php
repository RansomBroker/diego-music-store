<div class="w-full max-w-md">
    <!-- Back Link to Portal -->
    <div class="mb-6 flex justify-start">
        <a href="/" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-blue-400 transition-colors group">
            <i class="ph-bold ph-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            <span>Kembali ke Portal</span>
        </a>
    </div>

    <!-- Main Login Card -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-2xl rounded-3xl p-8 transition-colors duration-200 relative overflow-hidden">
        <!-- Accent Glow -->
        <div class="absolute -right-16 -top-16 w-32 h-32 rounded-full bg-primary/5"></div>
        
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-14 h-14 rounded-2xl bg-primary text-white flex items-center justify-center mx-auto mb-4 shadow-lg shadow-blue-500/25">
                <i class="ph-bold ph-shopping-bag-open text-2xl"></i>
            </div>
            <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Diego Music POS</h2>
            <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">Masuk ke sistem kasir penjualan ritel</p>
        </div>

        <!-- Form -->
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
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 focus:border-primary dark:focus:border-blue-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-primaryLight rounded-2xl outline-none transition-all text-sm font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400"
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
                        class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 focus:border-primary dark:focus:border-blue-400 focus:bg-white dark:focus:bg-slate-900 focus:ring-2 focus:ring-primaryLight rounded-2xl outline-none transition-all text-sm font-medium text-slate-800 dark:text-slate-100 placeholder-slate-400"
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
                        class="w-4 h-4 rounded border-slate-300 text-primary focus:ring-primaryLight cursor-pointer"
                    >
                    <span class="text-xs font-semibold text-slate-600 dark:text-slate-400">Ingat Saya</span>
                </label>
            </div>

            <!-- Submit Button -->
            <button 
                type="submit" 
                class="w-full py-3.5 px-4 bg-primary hover:bg-primaryHover text-white font-semibold rounded-2xl shadow-lg shadow-blue-500/20 active:scale-[0.98] transition-all flex items-center justify-center gap-2 cursor-pointer"
            >
                <!-- Loading indicator -->
                <span wire:loading wire:target="login" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <span>Masuk Kasir</span>
                <i wire:loading.remove wire:target="login" class="ph-bold ph-sign-in text-lg"></i>
            </button>
        </form>
    </div>

    <!-- Theme Switcher floating button inside card or under card -->
    <div class="mt-6 flex justify-center">
        <button onclick="toggleTheme()" class="w-10 h-10 rounded-xl bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-100 dark:hover:bg-slate-700 shadow-md border border-slate-200/50 dark:border-slate-700/50 transition-colors" title="Ubah Tema">
            <i class="ph-bold ph-sun dark:hidden text-lg"></i>
            <i class="ph-bold ph-moon hidden dark:block text-lg"></i>
        </button>
    </div>

    <script>
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
        }
    </script>
</div>
