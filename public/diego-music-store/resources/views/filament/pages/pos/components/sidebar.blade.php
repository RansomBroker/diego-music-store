@props(['selectedLogoUrl' => null])

@php
    $hasLogo = !empty($selectedLogoUrl) && $selectedLogoUrl !== '/storage' && $selectedLogoUrl !== '/storage/';
    
    // Route state checks
    $isDashboard       = request()->is('pos/front-office*');
    $isPos             = request()->is('pos') || request()->is('pos/login');
    $isSession         = request()->is('pos/session*');
    $isTransactions    = request()->is('pos/transactions*');
    $isDailyCash       = request()->is('pos/daily-cash*');
    $isCustomerPayments = request()->is('pos/customer-payments*');
    $isServiceManagement = request()->is('pos/service-management*');
    $isEmployees       = request()->is('pos/employees*');
    $isAttendances     = request()->is('pos/attendances*');
    $isAttendanceRadiuses = request()->is('pos/attendance-radiuses*');
    $isCommissions     = request()->is('pos/commissions*');
    $isViolations      = request()->is('pos/attendance-violations*');
    $isKpi             = request()->is('pos/kpi-performance*');
    $isPayroll         = request()->is('pos/payroll*');
    $isReports         = request()->is('pos/reports*');
    $isInputData       = request()->is('pos/customers*') || request()->is('pos/users*') || request()->is('pos/units*') || request()->is('pos/customer-labels*') || request()->is('pos/sale-categories*') || request()->is('pos/payment-methods*') || request()->is('pos/vouchers*');
    $isUtility         = request()->is('pos/privileges*') || request()->is('pos/store-profile*') || request()->is('pos/receipt-settings*') || request()->is('pos/barcode-print*') || request()->is('pos/branches*') || request()->is('pos/branch-performance*');
@endphp

<aside
    x-data="{
        isCollapsed: localStorage.getItem('pos_sidebar_collapsed') === 'true',
        openReports: {{ json_encode($isReports) }},
        openInputData: {{ json_encode($isInputData) }},
        openUtility: {{ json_encode($isUtility) }},
        toggleCollapse() {
            this.isCollapsed = !this.isCollapsed;
            localStorage.setItem('pos_sidebar_collapsed', this.isCollapsed);
        }
    }"
    @toggle-sidebar.window="toggleCollapse()"
    :class="isCollapsed ? 'w-20' : 'w-64'"
    class="h-screen max-h-screen bg-white dark:bg-slate-900 border-r border-slate-200/80 dark:border-slate-800 flex flex-col shadow-md z-20 flex-shrink-0 hidden md:flex transition-all duration-300 relative overflow-hidden"
>
    <!-- BRAND / LOGO HEADER -->
    <div class="px-4 py-4 border-b border-slate-100 dark:border-slate-800 flex items-center flex-shrink-0 h-16">
        <div class="flex items-center gap-3 min-w-0" x-show="!isCollapsed" x-cloak>
            <div class="w-9 h-9 rounded-xl overflow-hidden flex items-center justify-center shadow-md flex-shrink-0 {{ $hasLogo ? '' : 'bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-blue-500/20' }}">
                @if ($hasLogo)
                    <img src="{{ $selectedLogoUrl }}" alt="Store Logo" class="w-full h-full object-cover">
                @else
                    <i class="ph-bold ph-storefront text-lg"></i>
                @endif
            </div>
            <div class="min-w-0">
                <h2 class="text-xs font-black text-slate-900 dark:text-white tracking-tight truncate leading-tight">
                    Diego Music Store
                </h2>
                <div class="flex items-center gap-1 mt-0.5">
                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[9px] font-extrabold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Front Office</span>
                </div>
            </div>
        </div>

        <!-- Collapsed Icon Only Mode Logo -->
        <div class="w-full flex justify-center" x-show="isCollapsed">
            <div class="w-9 h-9 rounded-xl overflow-hidden flex items-center justify-center shadow-md {{ $hasLogo ? '' : 'bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-blue-500/20' }}">
                @if ($hasLogo)
                    <img src="{{ $selectedLogoUrl }}" alt="Store Logo" class="w-full h-full object-cover">
                @else
                    <i class="ph-bold ph-storefront text-lg"></i>
                @endif
            </div>
        </div>
    </div>

    <!-- MAIN SCROLLABLE NAVIGATION LIST -->
    <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto no-scrollbar">
        
        <!-- SECTION 1: UTAMA -->
        <div class="space-y-1">
            <div x-show="!isCollapsed" class="px-3 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">
                Menu Utama
            </div>

            <!-- Dashboard -->
            <a href="/pos/front-office"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isDashboard ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Dashboard Front Office' : ''">
                <i class="ph-bold ph-layout text-lg flex-shrink-0 {{ $isDashboard ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">Dashboard</span>
            </a>

            <!-- POS Kasir -->
            <a href="/pos"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isPos ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'POS Kasir Transaksi' : ''">
                <i class="ph-bold ph-squares-four text-lg flex-shrink-0 {{ $isPos ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">POS Kasir</span>
            </a>

            <!-- Sesi Kasir -->
            <a href="/pos/session"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isSession ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Sesi Kasir' : ''">
                <i class="ph-bold ph-clock-counter-clockwise text-lg flex-shrink-0 {{ $isSession ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">Sesi Kasir</span>
            </a>

            <!-- Histori Transaksi -->
            <a href="/pos/transactions"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isTransactions ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Daftar Transaksi' : ''">
                <i class="ph-bold ph-receipt text-lg flex-shrink-0 {{ $isTransactions ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">Daftar Transaksi</span>
            </a>
        </div>

        <!-- SECTION 2: OPERASIONAL & KAS -->
        <div class="space-y-1">
            <div x-show="!isCollapsed" class="px-3 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">
                Operasional & Keuangan
            </div>

            <!-- Kas Harian -->
            <a href="/pos/daily-cash"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isDailyCash ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Arus Kas Harian' : ''">
                <i class="ph-bold ph-wallet text-lg flex-shrink-0 {{ $isDailyCash ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">Kas Harian</span>
            </a>

            <!-- Pelunasan Piutang -->
            <a href="/pos/customer-payments"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isCustomerPayments ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Pelunasan Piutang' : ''">
                <i class="ph-bold ph-hand-coins text-lg flex-shrink-0 {{ $isCustomerPayments ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">Pelunasan Piutang</span>
            </a>

            <!-- Barang Service -->
            <a href="/pos/service-management"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isServiceManagement ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Manajemen Service' : ''">
                <i class="ph-bold ph-wrench text-lg flex-shrink-0 {{ $isServiceManagement ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">Service</span>
            </a>
        </div>

        <!-- SECTION 3: SDM & KARYAWAN -->
        <div class="space-y-1">
            <div x-show="!isCollapsed" class="px-3 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">
                SDM & Kehadiran
            </div>

            <!-- Data Karyawan -->
            <a href="/pos/employees"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isEmployees ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Data Personel Karyawan' : ''">
                <i class="ph-bold ph-user-gear text-lg flex-shrink-0 {{ $isEmployees ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">Karyawan</span>
            </a>

            <!-- Presensi Karyawan -->
            <a href="/pos/attendances"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isAttendances ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Presensi Staf' : ''">
                <i class="ph-bold ph-calendar-check text-lg flex-shrink-0 {{ $isAttendances ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">Presensi</span>
            </a>

            <!-- Komisi Sales -->
            <a href="/pos/commissions"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isCommissions ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Komisi Sales' : ''">
                <i class="ph-bold ph-percent text-lg flex-shrink-0 {{ $isCommissions ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">Komisi Sales</span>
            </a>

            <!-- KPI & Bonus -->
            <a href="/pos/kpi-performance"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isKpi ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Performance KPI' : ''">
                <i class="ph-bold ph-trophy text-lg flex-shrink-0 {{ $isKpi ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">KPI & Bonus</span>
            </a>

            <!-- Payroll Gaji -->
            <a href="/pos/payroll"
               class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-xs transition-all {{ $isPayroll ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
               :class="isCollapsed ? 'justify-center px-0' : ''"
               :title="isCollapsed ? 'Payroll Gaji' : ''">
                <i class="ph-bold ph-bank text-lg flex-shrink-0 {{ $isPayroll ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                <span x-show="!isCollapsed" class="truncate">Payroll Gaji</span>
            </a>
        </div>

        <!-- SECTION 4: MASTER DATA & LAPORAN -->
        <div class="space-y-1">
            <div x-show="!isCollapsed" class="px-3 text-[10px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest mb-1.5">
                Master Data & Laporan
            </div>

            <!-- Dropdown 1: Laporan ERP (Parent Icon, Submenu Lines without Icon) -->
            <div class="space-y-1">
                <button
                    @click="if (isCollapsed) { isCollapsed = false; openReports = true; } else { openReports = !openReports; }"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $isReports ? 'text-primary dark:text-blue-400 bg-primary/5 dark:bg-blue-950/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                    :class="isCollapsed ? 'justify-center px-0' : ''"
                    :title="isCollapsed ? 'Laporan ERP' : ''"
                >
                    <div class="flex items-center gap-3">
                        <i class="ph-bold ph-chart-pie-slice text-lg flex-shrink-0 {{ $isReports ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span x-show="!isCollapsed" class="truncate">Laporan ERP</span>
                    </div>
                    <i x-show="!isCollapsed" class="ph-bold ph-caret-down text-xs transition-transform duration-200" :class="{ 'rotate-180': openReports }"></i>
                </button>

                <!-- Submenu Connector with Border Guide (No Icons inside Submenu) -->
                <div x-show="openReports && !isCollapsed" x-cloak class="ml-4 pl-3 border-l-2 border-slate-200 dark:border-slate-700/80 space-y-1 text-xs pt-1">
                    <a href="{{ route('pos.reports.sales') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.reports.sales') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Laporan Penjualan
                    </a>
                    <a href="{{ route('pos.reports.ar-aging') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.reports.ar-aging') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Laporan Piutang
                    </a>
                    <a href="{{ route('pos.reports.daily-cash') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.reports.daily-cash') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Laporan Kas Harian
                    </a>
                    <a href="{{ route('pos.reports.stock-prices') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.reports.stock-prices') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Daftar Stok & Harga
                    </a>
                </div>
            </div>

            <!-- Dropdown 2: Input Master Data (Parent Icon, Submenu Lines without Icon) -->
            <div class="space-y-1">
                <button
                    @click="if (isCollapsed) { isCollapsed = false; openInputData = true; } else { openInputData = !openInputData; }"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $isInputData ? 'text-primary dark:text-blue-400 bg-primary/5 dark:bg-blue-950/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                    :class="isCollapsed ? 'justify-center px-0' : ''"
                    :title="isCollapsed ? 'Input Master Data' : ''"
                >
                    <div class="flex items-center gap-3">
                        <i class="ph-bold ph-database text-lg flex-shrink-0 {{ $isInputData ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span x-show="!isCollapsed" class="truncate">Input Master Data</span>
                    </div>
                    <i x-show="!isCollapsed" class="ph-bold ph-caret-down text-xs transition-transform duration-200" :class="{ 'rotate-180': openInputData }"></i>
                </button>

                <!-- Submenu Connector with Border Guide (No Icons inside Submenu) -->
                <div x-show="openInputData && !isCollapsed" x-cloak class="ml-4 pl-3 border-l-2 border-slate-200 dark:border-slate-700/80 space-y-1 text-xs pt-1">
                    <a href="{{ route('pos.customers') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.customers') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Data Pelanggan
                    </a>
                    <a href="{{ route('pos.users') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.users') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Data User Login
                    </a>
                    <a href="{{ route('pos.units') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.units') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Satuan Barang
                    </a>
                    <a href="{{ route('pos.sale-categories') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.sale-categories') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Kategori Penjualan
                    </a>
                    <a href="{{ route('pos.payment-methods') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.payment-methods') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Metode Pembayaran
                    </a>
                    <a href="{{ route('pos.vouchers') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.vouchers') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Data Voucher Promo
                    </a>
                </div>
            </div>

            <!-- Dropdown 3: Pengaturan & Utility (Parent Icon, Submenu Lines without Icon) -->
            <div class="space-y-1">
                <button
                    @click="if (isCollapsed) { isCollapsed = false; openUtility = true; } else { openUtility = !openUtility; }"
                    class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl text-xs font-semibold transition-all {{ $isUtility ? 'text-primary dark:text-blue-400 bg-primary/5 dark:bg-blue-950/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                    :class="isCollapsed ? 'justify-center px-0' : ''"
                    :title="isCollapsed ? 'Pengaturan & Utility' : ''"
                >
                    <div class="flex items-center gap-3">
                        <i class="ph-bold ph-gear text-lg flex-shrink-0 {{ $isUtility ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                        <span x-show="!isCollapsed" class="truncate">Pengaturan & Utility</span>
                    </div>
                    <i x-show="!isCollapsed" class="ph-bold ph-caret-down text-xs transition-transform duration-200" :class="{ 'rotate-180': openUtility }"></i>
                </button>

                <!-- Submenu Connector with Border Guide (No Icons inside Submenu) -->
                <div x-show="openUtility && !isCollapsed" x-cloak class="ml-4 pl-3 border-l-2 border-slate-200 dark:border-slate-700/80 space-y-1 text-xs pt-1">
                    <a href="{{ route('pos.privileges') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.privileges') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Privilege Role User
                    </a>
                    <a href="{{ route('pos.store-profile') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.store-profile') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Profil & Nama Toko
                    </a>
                    <a href="{{ route('pos.branches') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.branches') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Manajemen Cabang
                    </a>
                    <a href="{{ route('pos.branch-performance') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.branch-performance') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/40 dark:bg-blue-950/40' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Performa Cabang Toko
                    </a>
                    <a href="{{ route('pos.receipt-settings') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.receipt-settings') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Setting Struk & Invoice
                    </a>
                    <a href="{{ route('pos.barcode-print') }}"
                       class="block py-1.5 px-2.5 rounded-lg transition-colors {{ request()->routeIs('pos.barcode-print') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium' }}">
                        Cetak Barcode Item
                    </a>
                </div>
            </div>
        </div>

    </nav>

    <!-- SIDEBAR FOOTER (BACKOFFICE ACCESSIBLE LINK) -->
    <div class="p-3 border-t border-slate-100 dark:border-slate-800 flex-shrink-0">
        <a href="/backoffice"
           class="flex items-center justify-between px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-800/90 text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-700 dark:hover:text-emerald-400 transition-all group"
           :class="isCollapsed ? 'justify-center px-0' : ''"
           :title="isCollapsed ? 'Panel Backoffice Admin' : ''">
            <div class="flex items-center gap-3">
                <div class="w-7 h-7 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold flex-shrink-0">
                    <i class="ph-bold ph-house text-base group-hover:scale-110 transition-transform"></i>
                </div>
                <div class="text-left" x-show="!isCollapsed">
                    <div class="text-xs font-black">Panel Backoffice</div>
                    <div class="text-[9px] text-slate-400 dark:text-slate-500">Kelola Admin ERP</div>
                </div>
            </div>
            <i x-show="!isCollapsed" class="ph-bold ph-arrow-right text-xs text-slate-400 group-hover:translate-x-0.5 transition-transform"></i>
        </a>
    </div>
</aside>
