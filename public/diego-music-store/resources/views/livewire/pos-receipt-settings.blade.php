<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Toast Notification Listener -->
    <x-pos.toast />

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Setting Struk & Invoice"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header & Breadcrumbs -->
                @include('livewire.pos-receipt-settings.components.header-bar')

                <!-- Form & Live Thermal Receipt Preview Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left 2 Cols: Setting Form -->
                    @include('livewire.pos-receipt-settings.components.settings-form')

                    <!-- Right Column: Live Thermal Receipt Simulator -->
                    @include('livewire.pos-receipt-settings.components.receipt-preview')
                </div>

            </div>
        </div>
    </main>
</div>
