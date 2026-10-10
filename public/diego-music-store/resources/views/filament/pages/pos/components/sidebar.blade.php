@props(['selectedLogoUrl' => null])

@php
    $hasLogo = !empty($selectedLogoUrl) && $selectedLogoUrl !== '/storage' && $selectedLogoUrl !== '/storage/';
    
    // Route state checks
    $isDashboard       = request()->is('pos/front-office*');
    $isPos             = request()->is('pos') || request()->is('pos/login');
    $isSession         = request()->is('pos/session*');
    $isTransactions    = request()->is('pos/transactions*');
    $isCustomerDeposits = request()->is('pos/customer-deposits*');
    $isDailyCash       = request()->is('pos/daily-cash*');
    $isCustomerPayments = request()->is('pos/customer-payments*');
    $isServiceManagement = request()->is('pos/service-management*');
    $isEmployees       = request()->is('pos/employees*');
    $isEmployeeTasks   = request()->is('pos/employee-tasks*');
    $isAttendances     = request()->is('pos/attendances*');
    $isDayOffCalendar  = request()->is('pos/day-off-calendar*');
    $isAttendanceRadiuses = request()->is('pos/attendance-radiuses*');
    $isCommissions     = request()->is('pos/commissions*');
    $isViolations      = request()->is('pos/attendance-violations*');
    $isKpi             = request()->is('pos/kpi-performance*');
    $isPayroll         = request()->is('pos/payroll*');
    $isReports         = request()->is('pos/reports*');
    $isInputData       = request()->is('pos/customers*') || request()->is('pos/users*') || request()->is('pos/units*') || request()->is('pos/customer-labels*') || request()->is('pos/sale-categories*') || request()->is('pos/payment-methods*') || request()->is('pos/vouchers*');
    $isUtility         = request()->is('pos/privileges*') || request()->is('pos/store-profile*') || request()->is('pos/receipt-settings*') || request()->is('pos/barcode-print*') || request()->is('pos/branches*') || request()->is('pos/branch-performance*') || request()->is('pos/whatsapp-settings*');
    $canManageAdmin    = auth()->check() && auth()->user()->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']);
@endphp

<div
    x-data="{
        isCollapsed: localStorage.getItem('pos_sidebar_collapsed') === 'true',
        isMobileOpen: false,
        openReports: {{ json_encode($isReports) }},
        openInputData: {{ json_encode($isInputData) }},
        openUtility: {{ json_encode($isUtility) }},
        toggleSidebar() {
            if (window.innerWidth < 768) {
                this.isMobileOpen = !this.isMobileOpen;
            } else {
                this.isCollapsed = !this.isCollapsed;
                localStorage.setItem('pos_sidebar_collapsed', this.isCollapsed);
            }
        },
        closeMobile() {
            this.isMobileOpen = false;
        },
        get isCompact() {
            return this.isCollapsed && !this.isMobileOpen;
        }
    }"
    @toggle-sidebar.window="toggleSidebar()"
    @keydown.escape.window="closeMobile()"
    @resize.window="if (window.innerWidth >= 768) closeMobile()"
    class="w-0 flex-shrink-0 z-40 transition-[width]"
    :class="isCompact ? 'md:w-20' : 'md:w-64'"
>
    <!-- BACKDROP OVERLAY (MOBILE ONLY) -->
    <div
        x-show="isMobileOpen"
        @click="closeMobile()"
        x-transition:enter="transition-opacity ease-linear duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-slate-950/80 z-40 md:hidden"
        x-cloak
    ></div>

    <!-- MAIN SIDEBAR NAVIGATION -->
    <aside
        class="h-screen max-h-screen bg-white dark:bg-slate-900 border-r border-slate-200/80 dark:border-slate-800 flex flex-col shadow-2xl md:shadow-md transition-transform ease-in-out duration-300 overflow-hidden fixed inset-y-0 left-0 z-50 md:static md:z-auto w-72 md:w-full -translate-x-full md:translate-x-0"
        :class="isMobileOpen ? 'translate-x-0' : '-translate-x-full md:translate-x-0'"
    >
        <!-- BRAND / LOGO HEADER -->
        <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between flex-shrink-0 h-[72px]">
            <div class="flex items-center gap-3 min-w-0" x-show="!isCompact" x-cloak>
                @if ($hasLogo)
                    <div class="w-20 h-20 flex items-center justify-center flex-shrink-0">
                        <img src="{{ $selectedLogoUrl }}" alt="Store Logo" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-12 h-12 rounded-xl overflow-hidden flex items-center justify-center shadow-md bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-blue-500/20 flex-shrink-0">
                        <i class="ph-bold ph-storefront text-2xl"></i>
                    </div>
                @endif
                <div class="min-w-0">
                    <h2 class="text-sm md:text-base font-black text-slate-900 dark:text-white tracking-tight truncate leading-tight">
                        Diego Music Store
                    </h2>
                    <div class="flex items-center gap-1.5 mt-0.5">
                        <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span class="text-[10px] md:text-[11px] font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider">Front Office</span>
                    </div>
                </div>
            </div>

            <!-- Mobile Close Button ('X') -->
            <button
                type="button"
                @click="closeMobile()"
                class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 text-slate-500 hover:text-slate-800 dark:hover:text-white flex items-center justify-center md:hidden transition-colors cursor-pointer flex-shrink-0"
                title="Tutup Menu"
                aria-label="Tutup Sidebar"
            >
                <i class="ph-bold ph-x text-base"></i>
            </button>

            <!-- Collapsed Icon Only Mode Logo (Desktop) -->
            <div class="w-full flex justify-center" x-show="isCompact" x-cloak>
                @if ($hasLogo)
                    <div class="w-12 h-12 flex items-center justify-center flex-shrink-0">
                        <img src="{{ $selectedLogoUrl }}" alt="Store Logo" class="w-full h-full object-contain">
                    </div>
                @else
                    <div class="w-12 h-12 rounded-xl overflow-hidden flex items-center justify-center shadow-md bg-gradient-to-br from-blue-600 to-indigo-600 text-white shadow-blue-500/20 flex-shrink-0">
                        <i class="ph-bold ph-storefront text-2xl"></i>
                    </div>
                @endif
            </div>
        </div>

        <!-- MAIN SCROLLABLE NAVIGATION LIST -->
        <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto no-scrollbar">
            
            <!-- SECTION 1: UTAMA -->
            <div class="space-y-1">
                <div x-show="!isCompact" class="px-3.5 text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                    Menu Utama
                </div>

                <!-- Dashboard -->
                <a href="/pos/front-office"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isDashboard ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Dashboard Front Office' : ''">
                    <i class="ph-bold ph-layout text-xl flex-shrink-0 {{ $isDashboard ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Dashboard</span>
                </a>

                <!-- POS Kasir -->
                <a href="/pos"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isPos ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'POS Kasir Transaksi' : ''">
                    <i class="ph-bold ph-squares-four text-xl flex-shrink-0 {{ $isPos ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">POS Kasir</span>
                </a>

                <!-- Sesi Kasir -->
                <a href="/pos/session"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isSession ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Sesi Kasir' : ''">
                    <i class="ph-bold ph-clock-counter-clockwise text-xl flex-shrink-0 {{ $isSession ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Sesi Kasir</span>
                </a>

                <!-- Histori Transaksi -->
                <a href="/pos/transactions?fromDate={{ now()->format('Y-m-d') }}&toDate={{ now()->format('Y-m-d') }}"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isTransactions ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Daftar Transaksi' : ''">
                    <i class="ph-bold ph-receipt text-xl flex-shrink-0 {{ $isTransactions ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Daftar Transaksi</span>
                </a>

                <!-- Deposit Pelanggan -->
                <a href="/pos/customer-deposits"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isCustomerDeposits ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Deposit Pelanggan' : ''">
                    <i class="ph-bold ph-piggy-bank text-xl flex-shrink-0 {{ $isCustomerDeposits ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Deposit Pelanggan</span>
                </a>
            </div>

            <!-- SECTION 2: OPERASIONAL & KAS -->
            <div class="space-y-1">
                <div x-show="!isCompact" class="px-3.5 text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                    Operasional & Keuangan
                </div>

                <!-- Kas Harian -->
                <a href="/pos/daily-cash"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isDailyCash ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Arus Kas Harian' : ''">
                    <i class="ph-bold ph-wallet text-xl flex-shrink-0 {{ $isDailyCash ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Kas Harian</span>
                </a>

                <!-- Pelunasan Piutang -->
                <a href="/pos/customer-payments"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isCustomerPayments ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Pelunasan Piutang' : ''">
                    <i class="ph-bold ph-hand-coins text-xl flex-shrink-0 {{ $isCustomerPayments ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Pelunasan Piutang</span>
                </a>

                <!-- Barang Service -->
                <a href="/pos/service-management"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isServiceManagement ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Manajemen Service' : ''">
                    <i class="ph-bold ph-wrench text-xl flex-shrink-0 {{ $isServiceManagement ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Service</span>
                </a>
            </div>

            <!-- SECTION 3: SDM & KARYAWAN -->
            <div class="space-y-1">
                <div x-show="!isCompact" class="px-3.5 text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                    SDM & Kehadiran
                </div>

                <!-- Data Karyawan -->
                @if($canManageAdmin)
                <a href="/pos/employees"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isEmployees ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Data Personel Karyawan' : ''">
                    <i class="ph-bold ph-user-gear text-xl flex-shrink-0 {{ $isEmployees ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Karyawan</span>
                </a>
                @endif

                <!-- Tugas Karyawan -->
                <a href="/pos/employee-tasks"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isEmployeeTasks ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Tugas Karyawan' : ''">
                    <i class="ph-bold ph-clipboard-text text-xl flex-shrink-0 {{ $isEmployeeTasks ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Tugas Karyawan</span>
                </a>

                <!-- Kalender Jadwal Off -->
                <a href="/pos/day-off-calendar"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isDayOffCalendar ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Kalender Jadwal Off' : ''">
                    <i class="ph-bold ph-calendar-blank text-xl flex-shrink-0 {{ $isDayOffCalendar ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Kalender Jadwal Off</span>
                </a>

                <!-- Presensi Karyawan -->
                <a href="/pos/attendances"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isAttendances ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Presensi Staf' : ''">
                    <i class="ph-bold ph-calendar-check text-xl flex-shrink-0 {{ $isAttendances ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Presensi</span>
                </a>

                <!-- Komisi Sales -->
                <a href="/pos/commissions"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isCommissions ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Komisi Sales' : ''">
                    <i class="ph-bold ph-percent text-xl flex-shrink-0 {{ $isCommissions ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Komisi Sales</span>
                </a>

                <!-- KPI & Bonus -->
                <a href="/pos/kpi-performance"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isKpi ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Performance KPI' : ''">
                    <i class="ph-bold ph-trophy text-xl flex-shrink-0 {{ $isKpi ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">KPI & Bonus</span>
                </a>

                <!-- Payroll Gaji -->
                @if($canManageAdmin)
                <a href="/pos/payroll"
                   @click="if (window.innerWidth < 768) closeMobile()"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm transition-colors {{ $isPayroll ? 'bg-primary/10 dark:bg-blue-950/60 text-primary dark:text-blue-400 font-extrabold border-l-4 border-primary dark:border-blue-400 shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 hover:text-slate-900 dark:hover:text-white font-semibold' }}"
                   :class="isCompact ? 'justify-center px-0' : ''"
                   :title="isCompact ? 'Payroll Gaji' : ''">
                    <i class="ph-bold ph-bank text-xl flex-shrink-0 {{ $isPayroll ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                    <span x-show="!isCompact" class="truncate font-semibold">Payroll Gaji</span>
                </a>
                @endif
            </div>

            <!-- SECTION 4: MASTER DATA & LAPORAN -->
            <div class="space-y-1">
                <div x-show="!isCompact" class="px-3.5 text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-wider mb-2">
                    Master Data & Laporan
                </div>

                <!-- Dropdown 1: Laporan ERP -->
                <div class="space-y-1">
                    <button
                        @click="if (isCompact) { isCollapsed = false; openReports = true; } else { openReports = !openReports; }"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ $isReports ? 'text-primary dark:text-blue-400 bg-primary/5 dark:bg-blue-950/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        :class="isCompact ? 'justify-center px-0' : ''"
                        :title="isCompact ? 'Laporan ERP' : ''"
                    >
                        <div class="flex items-center gap-3">
                            <i class="ph-bold ph-chart-pie-slice text-xl flex-shrink-0 {{ $isReports ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                            <span x-show="!isCompact" class="truncate font-semibold">Laporan ERP</span>
                        </div>
                        <i x-show="!isCompact" class="ph-bold ph-caret-down text-sm transition-transform duration-200" :class="{ 'rotate-180': openReports }"></i>
                    </button>

                    <!-- Submenu Connector -->
                    <div x-show="openReports && !isCompact" x-cloak class="ml-4 pl-3.5 border-l-2 border-slate-200 dark:border-slate-700/80 space-y-1 text-[13.5px] pt-1.5">
                        <a href="{{ route('pos.reports.sales') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.reports.sales') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Laporan Penjualan
                        </a>
                        <a href="{{ route('pos.reports.ar-aging') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.reports.ar-aging') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Laporan Piutang
                        </a>
                        <a href="{{ route('pos.reports.daily-cash') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.reports.daily-cash') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Laporan Kas Harian
                        </a>
                        <a href="{{ route('pos.reports.stock-prices') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.reports.stock-prices') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Daftar Stok & Harga
                        </a>
                    </div>
                </div>

                <!-- Dropdown 2: Input Master Data -->
                <div class="space-y-1">
                    <button
                        @click="if (isCompact) { isCollapsed = false; openInputData = true; } else { openInputData = !openInputData; }"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ $isInputData ? 'text-primary dark:text-blue-400 bg-primary/5 dark:bg-blue-950/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        :class="isCompact ? 'justify-center px-0' : ''"
                        :title="isCompact ? 'Input Master Data' : ''"
                    >
                        <div class="flex items-center gap-3">
                            <i class="ph-bold ph-database text-xl flex-shrink-0 {{ $isInputData ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                            <span x-show="!isCompact" class="truncate font-semibold">Input Master Data</span>
                        </div>
                        <i x-show="!isCompact" class="ph-bold ph-caret-down text-sm transition-transform duration-200" :class="{ 'rotate-180': openInputData }"></i>
                    </button>

                    <!-- Submenu Connector -->
                    <div x-show="openInputData && !isCompact" x-cloak class="ml-4 pl-3.5 border-l-2 border-slate-200 dark:border-slate-700/80 space-y-1 text-[13.5px] pt-1.5">
                        <a href="{{ route('pos.customers') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.customers') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Data Pelanggan
                        </a>
                        @if($canManageAdmin)
                        <a href="{{ route('pos.users') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.users') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Data User Login
                        </a>
                        <a href="{{ route('pos.units') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.units') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Satuan Barang
                        </a>
                        <a href="{{ route('pos.sale-categories') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.sale-categories') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Kategori Penjualan
                        </a>
                        <a href="{{ route('pos.payment-methods') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.payment-methods') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Metode Pembayaran
                        </a>
                        <a href="{{ route('pos.vouchers') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.vouchers') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Data Voucher Promo
                        </a>
                        @endif
                    </div>
                </div>

                <!-- Dropdown 3: Pengaturan & Utility -->
                @if($canManageAdmin)
                <div class="space-y-1">
                    <button
                        @click="if (isCompact) { isCollapsed = false; openUtility = true; } else { openUtility = !openUtility; }"
                        class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-colors {{ $isUtility ? 'text-primary dark:text-blue-400 bg-primary/5 dark:bg-blue-950/30' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800' }}"
                        :class="isCompact ? 'justify-center px-0' : ''"
                        :title="isCompact ? 'Pengaturan & Utility' : ''"
                    >
                        <div class="flex items-center gap-3">
                            <i class="ph-bold ph-gear text-xl flex-shrink-0 {{ $isUtility ? 'text-primary dark:text-blue-400' : 'text-slate-400 dark:text-slate-500' }}"></i>
                            <span x-show="!isCompact" class="truncate font-semibold">Pengaturan & Utility</span>
                        </div>
                        <i x-show="!isCompact" class="ph-bold ph-caret-down text-sm transition-transform duration-200" :class="{ 'rotate-180': openUtility }"></i>
                    </button>

                    <!-- Submenu Connector -->
                    <div x-show="openUtility && !isCompact" x-cloak class="ml-4 pl-3.5 border-l-2 border-slate-200 dark:border-slate-700/80 space-y-1 text-[13.5px] pt-1.5">
                        <a href="{{ route('pos.privileges') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.privileges') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Privilege Role User
                        </a>
                        <a href="{{ route('pos.store-profile') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.store-profile') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Profil & Nama Toko
                        </a>
                        <a href="{{ route('pos.branches') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.branches') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Manajemen Cabang
                        </a>
                        <a href="{{ route('pos.whatsapp-settings') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.whatsapp-settings') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Setting WA Fonnte
                        </a>
                        <a href="{{ route('pos.branch-performance') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.branch-performance') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/40 dark:bg-blue-950/40' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Performa Cabang Toko
                        </a>
                        <a href="{{ route('pos.receipt-settings') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.receipt-settings') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Setting Struk & Printer Hardware
                        </a>
                        <a href="{{ route('pos.barcode-print') }}"
                           @click="if (window.innerWidth < 768) closeMobile()"
                           class="block py-2 px-3 rounded-xl transition-colors {{ request()->routeIs('pos.barcode-print') ? 'text-primary dark:text-blue-400 font-bold bg-primary-light/50 dark:bg-blue-950/50' : 'text-slate-500 dark:text-slate-400 hover:text-slate-900 dark:hover:text-white font-medium hover:bg-slate-100/80 dark:hover:bg-slate-800/80' }}">
                            Cetak Barcode Item
                        </a>
                    </div>
                </div>
                @endif
            </div>

        </nav>

        <!-- SIDEBAR FOOTER (BACKOFFICE ACCESSIBLE LINK) -->
        @if($canManageAdmin)
        <div class="p-3 border-t border-slate-100 dark:border-slate-800 flex-shrink-0">
            <a href="/backoffice"
               class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-slate-100 dark:bg-slate-800/90 text-slate-700 dark:text-slate-200 hover:bg-emerald-50 dark:hover:bg-emerald-950/40 hover:text-emerald-700 dark:hover:text-emerald-400 transition-colors group"
               :class="isCompact ? 'justify-center px-0' : ''"
               :title="isCompact ? 'Panel Backoffice Admin' : ''">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-xl bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold flex-shrink-0">
                        <i class="ph-bold ph-house text-lg group-hover:scale-110 transition-transform"></i>
                    </div>
                    <div class="text-left" x-show="!isCompact">
                        <div class="text-sm font-black text-slate-900 dark:text-slate-100">Panel Backoffice</div>
                        <div class="text-[11px] font-semibold text-slate-400 dark:text-slate-500">Kelola Admin ERP</div>
                    </div>
                </div>
                <i x-show="!isCompact" class="ph-bold ph-arrow-right text-sm text-slate-400 group-hover:translate-x-0.5 transition-transform"></i>
            </a>
        </div>
        @endif
    </aside>
</div>
