@props(['selectedLogoUrl' => null])

@php
    $hasLogo = !empty($selectedLogoUrl) && $selectedLogoUrl !== '/storage' && $selectedLogoUrl !== '/storage/';
@endphp

@php
    $isPos          = request()->is('pos') || request()->is('pos/login');
    $isSession      = request()->is('pos/session*');
    $isTransactions = request()->is('pos/transactions*');
    $isDailyCash    = request()->is('pos/daily-cash*');
    $isSupplierPayments = request()->is('pos/supplier-payments*');
    $isDashboard    = request()->is('pos/front-office*');
    $isInputData    = request()->is('pos/customers*') || request()->is('pos/users*') || request()->is('pos/units*') || request()->is('pos/customer-labels*') || request()->is('pos/sale-categories*') || request()->is('pos/payment-methods*');
@endphp

<aside
    x-data="{
        inputDataOpen: false,
        buttonY: 0,
        updatePosition() {
            const trigger = document.getElementById('input-data-trigger');
            if (trigger) {
                const rect = trigger.getBoundingClientRect();
                this.buttonY = rect.top;
            }
        }
    }"
    @resize.window="if (inputDataOpen) updatePosition()"
    class="w-28 h-screen max-h-screen bg-white dark:bg-slate-800 border-r border-slate-200 dark:border-slate-700 flex flex-col items-center py-6 shadow-sm z-10 flex-shrink-0 hidden md:flex transition-colors relative overflow-hidden"
>
    <!-- Logo -->
    <div class="w-12 h-12 rounded-xl overflow-hidden flex items-center justify-center mb-6 shadow-md flex-shrink-0 {{ $hasLogo ? '' : 'bg-primary text-white shadow-blue-500/30' }}">
        @if ($hasLogo)
            <img src="{{ $selectedLogoUrl }}" alt="Store Logo" class="w-full h-full object-cover">
        @else
            <i class="ph-bold ph-storefront text-2xl"></i>
        @endif
    </div>

    <!-- Menu Items (Scrollable Navigation) -->
    <nav
        @scroll="if (inputDataOpen) updatePosition()"
        class="flex flex-col gap-3 flex-1 w-full px-3 overflow-y-auto no-scrollbar pb-4"
    >

        {{-- Dashboard --}}
        @if ($isDashboard)
            <button class="w-full py-3 flex flex-col items-center justify-center text-primary dark:text-blue-400 bg-primary-light dark:bg-blue-950/40 rounded-xl transition-colors cursor-default">
                <i class="ph-fill ph-layout text-2xl mb-1"></i>
                <span class="text-[11px] font-semibold">Dashboard</span>
            </button>
        @else
            <a href="/pos/front-office" class="w-full py-3 flex flex-col items-center justify-center text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors">
                <i class="ph ph-layout text-2xl mb-1"></i>
                <span class="text-[11px] font-medium">Dashboard</span>
            </a>
        @endif

        {{-- POS Kasir --}}
        @if ($isPos)
            <button class="w-full py-3 flex flex-col items-center justify-center text-primary dark:text-blue-400 bg-primary-light dark:bg-blue-950/40 rounded-xl transition-colors cursor-default">
                <i class="ph-fill ph-squares-four text-2xl mb-1"></i>
                <span class="text-[11px] font-semibold">Kasir</span>
            </button>
        @else
            <a href="/pos" class="w-full py-3 flex flex-col items-center justify-center text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors">
                <i class="ph ph-squares-four text-2xl mb-1"></i>
                <span class="text-[11px] font-medium">Kasir</span>
            </a>
        @endif

        {{-- Sesi Kasir --}}
        @if ($isSession)
            <button class="w-full py-3 flex flex-col items-center justify-center text-primary dark:text-blue-400 bg-primary-light dark:bg-blue-950/40 rounded-xl transition-colors cursor-default">
                <i class="ph-fill ph-clock-counter-clockwise text-2xl mb-1"></i>
                <span class="text-[11px] font-semibold text-center leading-tight">Sesi Kasir</span>
            </button>
        @else
            <a href="/pos/session" class="w-full py-3 flex flex-col items-center justify-center text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors">
                <i class="ph ph-clock-counter-clockwise text-2xl mb-1"></i>
                <span class="text-[11px] font-medium text-center leading-tight">Sesi Kasir</span>
            </a>
        @endif

        {{-- List Transaksi --}}
        @if ($isTransactions)
            <button class="w-full py-3 flex flex-col items-center justify-center text-primary dark:text-blue-400 bg-primary-light dark:bg-blue-950/40 rounded-xl transition-colors cursor-default">
                <i class="ph-fill ph-receipt text-2xl mb-1"></i>
                <span class="text-[11px] font-semibold text-center leading-tight">Transaksi</span>
            </button>
        @else
            <a href="/pos/transactions" class="w-full py-3 flex flex-col items-center justify-center text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors">
                <i class="ph ph-receipt text-2xl mb-1"></i>
                <span class="text-[11px] font-medium text-center leading-tight">Transaksi</span>
            </a>
        @endif

        {{-- Kas Harian --}}
        @if ($isDailyCash)
            <button class="w-full py-3 flex flex-col items-center justify-center text-primary dark:text-blue-400 bg-primary-light dark:bg-blue-950/40 rounded-xl transition-colors cursor-default">
                <i class="ph-fill ph-wallet text-2xl mb-1"></i>
                <span class="text-[11px] font-semibold text-center leading-tight">Kas Harian</span>
            </button>
        @else
            <a href="/pos/daily-cash" class="w-full py-3 flex flex-col items-center justify-center text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors">
                <i class="ph ph-wallet text-2xl mb-1"></i>
                <span class="text-[11px] font-medium text-center leading-tight">Kas Harian</span>
            </a>
        @endif

        {{-- Pelunasan Hutang --}}
        @if ($isSupplierPayments)
            <button class="w-full py-3 flex flex-col items-center justify-center text-primary dark:text-blue-400 bg-primary-light dark:bg-blue-950/40 rounded-xl transition-colors cursor-default">
                <i class="ph-fill ph-credit-card text-2xl mb-1"></i>
                <span class="text-[11px] font-semibold text-center leading-tight">Pelunasan Hutang</span>
            </button>
        @else
            <a href="/pos/supplier-payments" class="w-full py-3 flex flex-col items-center justify-center text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors">
                <i class="ph ph-credit-card text-2xl mb-1"></i>
                <span class="text-[11px] font-medium text-center leading-tight">Pelunasan Hutang</span>
            </a>
        @endif

        {{-- Input Data (Flyout Trigger) --}}
        <div class="w-full">
            <button
                id="input-data-trigger"
                @click="inputDataOpen = !inputDataOpen; if(inputDataOpen) $nextTick(() => updatePosition())"
                :class="inputDataOpen || {{ json_encode($isInputData) }} ? 'text-primary dark:text-blue-400 bg-primary-light dark:bg-blue-950/40' : 'text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50'"
                class="w-full py-3 flex flex-col items-center justify-center rounded-xl transition-colors relative"
            >
                <i
                    :class="inputDataOpen || {{ json_encode($isInputData) }} ? 'ph-fill ph-database' : 'ph ph-database'"
                    class="text-2xl mb-1"
                ></i>
                <span class="text-[11px] font-medium flex items-center gap-0.5">
                    Input Data
                </span>
                {{-- Indikator expand --}}
                <span
                    x-show="inputDataOpen || {{ json_encode($isInputData) }}"
                    class="absolute -right-1 top-1/2 -translate-y-1/2 w-1.5 h-1.5 rounded-full bg-primary"
                    x-cloak
                ></span>
            </button>
        </div>

        {{-- Backoffice --}}
        <a href="/backoffice" class="w-full py-3 flex flex-col items-center justify-center text-slate-400 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-700/50 rounded-xl transition-colors">
            <i class="ph ph-house text-2xl mb-1"></i>
            <span class="text-[11px] font-medium">Backoffice</span>
        </a>
    </nav>

    <!-- Theme Switcher (Fixed Bottom) -->
    <div class="mt-auto pt-2 flex flex-col gap-4 w-full px-3 items-center flex-shrink-0">
        <!-- Dark Mode Toggle Button -->
        <button onclick="toggleDarkMode()" class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors cursor-pointer" title="Ubah Tema">
            <i class="ph-bold ph-sun dark:hidden text-lg"></i>
            <i class="ph-bold ph-moon hidden dark:block text-lg"></i>
        </button>
    </div>

    <!-- Teleported Flyout Submenu (Rendered outside the scrollable sidebar) -->
    <template x-teleport="body">
        <div
            x-show="inputDataOpen"
            x-transition:enter="transition ease-out duration-150"
            x-transition:enter-start="opacity-0 -translate-x-2 scale-95"
            x-transition:enter-end="opacity-100 translate-x-0 scale-100"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-100 translate-x-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-x-2 scale-95"
            @click.outside="const trigger = document.getElementById('input-data-trigger'); if ($event.target !== trigger && !trigger.contains($event.target)) inputDataOpen = false"
            :style="`position: fixed; left: 115px; top: ${buttonY}px; z-index: 9999;`"
            class="w-52 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-xl shadow-slate-900/10 dark:shadow-slate-900/40 overflow-hidden"
            x-cloak
        >
            {{-- Header Flyout --}}
            <div class="px-4 py-3 border-b border-slate-100 dark:border-slate-700 flex items-center gap-2">
                <i class="ph-fill ph-database text-base text-primary dark:text-blue-400"></i>
                <span class="text-xs font-black text-slate-700 dark:text-slate-200 uppercase tracking-wider">Input Data</span>
            </div>

            {{-- Sub-menu items --}}
            <div class="py-2">
                <a href="{{ route('pos.customers') }}"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold {{ request()->routeIs('pos.customers') ? 'text-primary dark:text-blue-400 bg-primary-light/50 dark:bg-blue-950/20' : 'text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 hover:bg-primary-light dark:hover:bg-blue-950/30' }} transition-colors group">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 group-hover:bg-primary-light dark:group-hover:bg-blue-950/40 flex items-center justify-center transition-colors flex-shrink-0">
                        <i class="ph ph-users text-sm {{ request()->routeIs('pos.customers') ? 'text-primary dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }} group-hover:text-primary dark:group-hover:text-blue-400 transition-colors"></i>
                    </div>
                    Data Pelanggan
                </a>

                <a href="{{ route('pos.users') }}"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold {{ request()->routeIs('pos.users') ? 'text-primary dark:text-blue-400 bg-primary-light/50 dark:bg-blue-950/20' : 'text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 hover:bg-primary-light dark:hover:bg-blue-950/30' }} transition-colors group">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 group-hover:bg-primary-light dark:group-hover:bg-blue-950/40 flex items-center justify-center transition-colors flex-shrink-0">
                        <i class="ph ph-user-circle text-sm {{ request()->routeIs('pos.users') ? 'text-primary dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }} group-hover:text-primary dark:group-hover:text-blue-400 transition-colors"></i>
                    </div>
                    Data User
                </a>

                <a href="{{ route('pos.units') }}"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold {{ request()->routeIs('pos.units') ? 'text-primary dark:text-blue-400 bg-primary-light/50 dark:bg-blue-950/20' : 'text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 hover:bg-primary-light dark:hover:bg-blue-950/30' }} transition-colors group">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 group-hover:bg-primary-light dark:group-hover:bg-blue-950/40 flex items-center justify-center transition-colors flex-shrink-0">
                        <i class="ph ph-ruler text-sm {{ request()->routeIs('pos.units') ? 'text-primary dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }} group-hover:text-primary dark:group-hover:text-blue-400 transition-colors"></i>
                    </div>
                    Satuan Barang
                </a>

                <a href="{{ route('pos.sale-categories') }}"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold {{ request()->routeIs('pos.sale-categories') ? 'text-primary dark:text-blue-400 bg-primary-light/50 dark:bg-blue-950/20' : 'text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 hover:bg-primary-light dark:hover:bg-blue-950/30' }} transition-colors group">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 group-hover:bg-primary-light dark:group-hover:bg-blue-950/40 flex items-center justify-center transition-colors flex-shrink-0">
                        <i class="ph ph-tag text-sm {{ request()->routeIs('pos.sale-categories') ? 'text-primary dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }} group-hover:text-primary dark:group-hover:text-blue-400 transition-colors"></i>
                    </div>
                    Kategori Penjualan
                </a>

                <a href="{{ route('pos.payment-methods') }}"
                   class="flex items-center gap-3 px-4 py-2.5 text-sm font-semibold {{ request()->routeIs('pos.payment-methods') ? 'text-primary dark:text-blue-400 bg-primary-light/50 dark:bg-blue-950/20' : 'text-slate-600 dark:text-slate-300 hover:text-primary dark:hover:text-blue-400 hover:bg-primary-light dark:hover:bg-blue-950/30' }} transition-colors group">
                    <div class="w-7 h-7 rounded-lg bg-slate-100 dark:bg-slate-700 group-hover:bg-primary-light dark:group-hover:bg-blue-950/40 flex items-center justify-center transition-colors flex-shrink-0">
                        <i class="ph ph-credit-card text-sm {{ request()->routeIs('pos.payment-methods') ? 'text-primary dark:text-blue-400' : 'text-slate-500 dark:text-slate-400' }} group-hover:text-primary dark:group-hover:text-blue-400 transition-colors"></i>
                    </div>
                    Metode Pembayaran
                </a>
            </div>
        </div>
    </template>

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
</aside>
