<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header — menggunakan komponen navbar POS global -->
        <x-pos.navbar
            pageTitle="Dashboard"
            backLabel="Backoffice"
            backUrl="/backoffice"
            :activeSessionInfo="$activeSessionInfo"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-8 no-scrollbar flex items-center justify-center">
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
    </main>
</div>
