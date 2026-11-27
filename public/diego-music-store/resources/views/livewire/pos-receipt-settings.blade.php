<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Toast Notification Listener -->
    <x-pos.toast />

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Setting Struk, Tagihan & Printer"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header & Breadcrumbs -->
                @include('livewire.pos-receipt-settings.components.header-bar')

                <!-- Tab Navigation Buttons -->
                <div class="flex flex-wrap items-center gap-3 p-1.5 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-sm w-fit">
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
                        Desain & Format Struk / Tagihan
                    </x-pos.utility.button>
                </div>

                <!-- Tab 1: Hardware Printer & Direct Print Agent (Test Page) -->
                @if ($activeTab === 'hardware')
                    <div>
                        @include('livewire.pos-receipt-settings.components.hardware-settings')
                    </div>
                @else
                    <!-- Tab 2: Form & Live Preview Struk / Tagihan / Invoice / Penawaran -->
                    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                        <!-- Left 3 Cols: Setting Form -->
                        <div class="lg:col-span-3">
                            @include('livewire.pos-receipt-settings.components.settings-form')
                        </div>

                        <!-- Right 2 Cols: Unified Live Preview (Struk, Tagihan, Invoice, Penawaran) -->
                        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl p-5">
                            @include('backoffice.receipt-settings.live-preview', [
                                'branchId' => $branchId,
                                'store_display_name' => $store_display_name,
                                'header_text' => $header_text,
                                'footer_text' => $footer_text,
                                'invoice_footer_notes' => $invoice_footer_notes,
                                'paper_width' => $paper_width,
                                'show_logo' => $show_logo,
                                'show_customer' => $show_customer,
                                'show_cashier' => $show_cashier,
                                'show_tax_details' => $show_tax_details,
                            ])
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </main>
</div>
