<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <x-pos.toast />

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Radius Presensi Cabang"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header & Breadcrumbs -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <!-- Breadcrumbs -->
                        <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                                <li class="inline-flex items-center">
                                    <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                        <a href="/pos/employees" class="hover:text-primary dark:hover:text-blue-400 transition-colors">Karyawan</a>
                                    </div>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                        <span class="text-slate-650 dark:text-slate-300 font-bold">Radius Presensi</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Pengaturan Radius Presensi Cabang</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tentukan titik koordinat GPS dan radius toleransi jarak (meter) untuk presensi karyawan per cabang</p>
                    </div>
                </div>

                <!-- Table Card Wrapper -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
                    @include('livewire.pos-attendance-radiuses.components.toolbar')
                    @include('livewire.pos-attendance-radiuses.components.radiuses-table')
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Edit Radius Peta Leaflet.js -->
    @include('livewire.pos-attendance-radiuses.components.map-modal')
</div>
