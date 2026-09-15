<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Toast Notification Listener -->
    <x-pos.toast />

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Payroll & Gaji Karyawan"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">
                <!-- Header & Filters -->
                @include('livewire.pos-payroll-management.components.header-filter')
                @include('livewire.pos-payroll-management.components.payroll-table')
            </div>
        </div>
    </main>

    <!-- Modals -->
    @include('livewire.pos-payroll-management.components.preview-modal')
    @include('livewire.pos-payroll-management.components.edit-item-modal')
</div>
