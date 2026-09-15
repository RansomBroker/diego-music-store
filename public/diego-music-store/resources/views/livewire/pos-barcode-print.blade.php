<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <x-pos.toast />

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Cetak Barcode Label Produk"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">
                <!-- Page Header (Title & Breadcrumbs) -->
                @include('livewire.pos-barcode-print.components.header-bar')

                <!-- Main Layout Grid -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Queue Table & Action Bar -->
                    <div class="lg:col-span-2 space-y-6">
                        @include('livewire.pos-barcode-print.components.queue-table')
                    </div>

                    <!-- Right Column: Settings & Live Preview -->
                    <div class="space-y-6">
                        @include('livewire.pos-barcode-print.components.print-settings')
                        @include('livewire.pos-barcode-print.components.preview-card')
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Product Search Modal -->
    @include('livewire.pos-barcode-print.components.product-modal')
</div>
