<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <x-pos.toast />

    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header POS Global -->
        <x-pos.navbar
            pageTitle="Potongan & Denda Presensi"
            backLabel="Dashboard"
            backUrl="/pos/front-office"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 no-scrollbar">
            <div class="w-full space-y-6">
                @include('livewire.pos-attendance-violations.components.header-filter')
                @include('livewire.pos-attendance-violations.components.summary-cards')

                <!-- Navigation Tabs & Tables Card -->
                <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700/80 shadow-sm overflow-hidden">
                    @include('livewire.pos-attendance-violations.components.tabs-navigation')

                    <div class="p-6">
                        @include('livewire.pos-attendance-violations.components.recap-table')
                        @include('livewire.pos-attendance-violations.components.rules-table')
                        @include('livewire.pos-attendance-violations.components.logs-table')
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Form Aturan Denda Pelanggaran -->
    @include('livewire.pos-attendance-violations.components.rule-modal')
</div>
