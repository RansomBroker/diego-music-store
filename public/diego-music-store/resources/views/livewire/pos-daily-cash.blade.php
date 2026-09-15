<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200" style="font-family: 'Outfit', sans-serif;">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Global Toast Notification Listener -->
        <x-pos.toast />

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Kas Harian"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header (Title & Breadcrumbs) -->
                @include('livewire.pos-daily-cash.components.header-banner')

                @if(!$activeSession)
                    <!-- Warning: No Active Session -->
                    @include('livewire.pos-daily-cash.components.no-session-warning')
                @else
                    <!-- Summary Metrics Grid -->
                    @include('livewire.pos-daily-cash.components.summary-metrics')

                    <!-- Transactions Table Card Wrapper (Filament Style) -->
                    @include('livewire.pos-daily-cash.components.transactions-table')
                @endif

            </div>
        </div>
    </main>

    <!-- Modal: Kas Masuk -->
    @include('livewire.pos-daily-cash.components.inflow-modal')

    <!-- Modal: Kas Keluar -->
    @include('livewire.pos-daily-cash.components.outflow-modal')
</div>
