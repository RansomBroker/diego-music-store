<!-- Navigation Tiles (Collapsible / Hidden for Owner by default) -->
<div x-data="{ openNav: {{ $isOwnerUser ? 'false' : 'true' }} }" class="space-y-3">
    @if ($isOwnerUser)
        <div class="flex items-center justify-between">
            <button @click="openNav = !openNav" type="button" class="text-xs font-bold text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200 transition flex items-center gap-1.5 cursor-pointer">
                <i class="ph-bold" :class="openNav ? 'ph-caret-up' : 'ph-caret-down'"></i>
                <span x-text="openNav ? 'Sembunyikan Navigasi Menu Staff' : 'Tampilkan Navigasi Menu Staff (Kasir, Sesi, Karyawan, Backoffice)'"></span>
            </button>
        </div>
    @endif

    <div x-show="openNav" x-collapse class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <!-- POS Kasir -->
        <a href="{{ route('pos') }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700 hover:border-primary dark:hover:border-blue-500 shadow-sm hover:shadow-lg hover:shadow-blue-500/10 transition-all flex flex-col items-center gap-3 text-center">
            <div class="w-12 h-12 rounded-2xl bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="ph-fill ph-shopping-cart text-2xl"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">POS Kasir</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Buka halaman kasir</p>
            </div>
        </a>

        <!-- Sesi Kasir -->
        <a href="{{ route('pos.session') }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700 hover:border-amber-500 dark:hover:border-amber-400 shadow-sm hover:shadow-lg hover:shadow-amber-500/10 transition-all flex flex-col items-center gap-3 text-center">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 dark:bg-amber-950/40 text-amber-600 dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="ph-fill ph-clock-counter-clockwise text-2xl"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">Sesi Kasir</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kelola sesi kasir</p>
            </div>
        </a>

        <!-- Performa Cabang -->
        <a href="{{ route('pos.branch-performance') }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700 hover:border-blue-500 dark:hover:border-blue-400 shadow-sm hover:shadow-lg hover:shadow-blue-500/10 transition-all flex flex-col items-center gap-3 text-center">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="ph-fill ph-buildings text-2xl"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">Performa Cabang</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Stok & Laba Rugi</p>
            </div>
        </a>

        <!-- Data Karyawan -->
        <a href="{{ route('pos.employees') }}" class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700 hover:border-purple-500 dark:hover:border-purple-400 shadow-sm hover:shadow-lg hover:shadow-purple-500/10 transition-all flex flex-col items-center gap-3 text-center">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 dark:bg-purple-950/40 text-purple-600 dark:text-purple-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="ph-fill ph-users-three text-2xl"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">Data Karyawan</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Kelola personel</p>
            </div>
        </a>

        <!-- Backoffice -->
        <a href="/backoffice" class="group bg-white dark:bg-slate-800 rounded-2xl p-5 border border-slate-200/60 dark:border-slate-700 hover:border-emerald-500 dark:hover:border-emerald-400 shadow-sm hover:shadow-lg hover:shadow-emerald-500/10 transition-all flex flex-col items-center gap-3 text-center">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                <i class="ph-fill ph-house text-2xl"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-slate-800 dark:text-slate-100 text-sm">Backoffice</h3>
                <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-0.5">Panel admin</p>
            </div>
        </a>
    </div>
</div>
