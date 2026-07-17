<div class="flex flex-col h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Simple Header -->
    <header class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-md px-6 py-4 border-b border-slate-200 dark:border-slate-700 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-slate-100">Front Office</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400">Selamat datang, {{ auth()->user()->name ?? 'Kasir' }}</p>
        </div>
        <div class="flex items-center gap-3">
            <!-- Cash Session Status -->
            @if (!empty($activeSessionInfo))
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl text-xs font-semibold" title="Sesi aktif sejak: {{ $activeSessionInfo['opened_at'] }}">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span class="hidden sm:inline">Sesi Aktif</span>
                </div>
            @else
                <div class="flex items-center gap-1.5 px-3 py-1.5 bg-rose-50 dark:bg-rose-950/40 text-rose-600 dark:text-rose-400 rounded-xl text-xs font-semibold">
                    <span class="h-2 w-2 rounded-full bg-rose-500"></span>
                    <span class="hidden sm:inline">Sesi Belum Dibuka</span>
                </div>
            @endif

            <!-- Dark Mode Toggle -->
            <button onclick="toggleDarkMode()" class="w-9 h-9 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors" title="Ubah Tema">
                <i class="ph-bold ph-sun dark:hidden text-base"></i>
                <i class="ph-bold ph-moon hidden dark:block text-base"></i>
            </button>
            <!-- User Info -->
            <div class="flex items-center gap-2">
                <div class="w-9 h-9 rounded-full bg-slate-200 dark:bg-slate-650 overflow-hidden border-2 border-white dark:border-slate-700 shadow-sm">
                    <img src="https://placehold.co/100x100/3b82f6/ffffff?text={{ substr(auth()->user()->name ?? 'AD', 0, 2) }}" alt="Profile" class="w-full h-full object-cover">
                </div>
                <div class="hidden sm:block text-left">
                    <div class="text-xs font-semibold text-slate-800 dark:text-slate-200">{{ auth()->user()->name ?? 'Administrator' }}</div>
                    <div class="text-[10px] text-slate-400 dark:text-slate-500 uppercase">{{ auth()->user()->roles()->first()?->name ?? 'Kasir' }}</div>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="flex-1 flex items-center justify-center p-8">
        <div class="max-w-2xl w-full">
            <!-- Navigation Tiles -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
                <!-- POS Kasir -->
                <a href="{{ route('pos') }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200/60 dark:border-slate-700 hover:border-primary dark:hover:border-blue-500 shadow-sm hover:shadow-lg hover:shadow-blue-500/10 transition-all flex flex-col items-center gap-4 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="ph-fill ph-shopping-cart text-3xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">POS Kasir</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Buka halaman kasir</p>
                    </div>
                </a>

                <!-- Sesi Kasir -->
                <a href="{{ route('pos.session') }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200/60 dark:border-slate-700 hover:border-amber-500 dark:hover:border-amber-400 shadow-sm hover:shadow-lg hover:shadow-amber-500/10 transition-all flex flex-col items-center gap-4 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="ph-fill ph-clock-counter-clockwise text-3xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Sesi Kasir</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola sesi kasir</p>
                    </div>
                </a>

                <!-- Backoffice -->
                <a href="/backoffice" class="group bg-white dark:bg-slate-800 rounded-2xl p-6 border border-slate-200/60 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-400 shadow-sm hover:shadow-lg hover:shadow-emerald-500/10 transition-all flex flex-col items-center gap-4 text-center">
                    <div class="w-16 h-16 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="ph-fill ph-house text-3xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 dark:text-slate-100 text-sm">Backoffice</h3>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Panel administrasi</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleDarkMode() {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('theme', 'light');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('theme', 'dark');
        }
    }
</script>
