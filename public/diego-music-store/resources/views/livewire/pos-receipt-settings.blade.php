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

                <!-- Tab Navigation Buttons -->
                <div class="flex items-center gap-3 p-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm w-fit">
                    <x-pos.utility.button
                        type="button"
                        wire:click="setTab('hardware')"
                        :variant="$activeTab === 'hardware' ? 'primary' : 'secondary'"
                        size="md"
                        icon="ph-printer"
                    >
                        Hardware Printer & Direct Agent (Test Page)
                    </x-pos.utility.button>

                    <x-pos.utility.button
                        type="button"
                        wire:click="setTab('design')"
                        :variant="$activeTab === 'design' ? 'primary' : 'secondary'"
                        size="md"
                        icon="ph-receipt"
                    >
                        Desain & Format Teks Struk
                    </x-pos.utility.button>
                </div>

                <!-- Tab 1: Hardware Printer & Direct Print Agent (Test Page) -->
                @if ($activeTab === 'hardware')
                    <div>
                        @include('livewire.pos-receipt-settings.components.hardware-settings')
                    </div>
                @else
                    <!-- Tab 2: Form & Live Thermal Receipt Preview Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left 2 Cols: Setting Form -->
                        @include('livewire.pos-receipt-settings.components.settings-form')

                        <!-- Right Column: Live Thermal Receipt Simulator -->
                        @include('livewire.pos-receipt-settings.components.receipt-preview')
                    </div>
                @endif

            </div>
        </div>
    </main>
</div>
