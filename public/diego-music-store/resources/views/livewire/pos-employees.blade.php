<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Toast Notification Listener -->
    <x-pos.toast />

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Data Karyawan"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Header & Toolbar -->
                @include('livewire.pos-employees.components.toolbar')

                <!-- Table Card Wrapper -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
                    @include('livewire.pos-employees.components.employees-table')
                </div>

            </div>
        </div>
    </main>

    <!-- Modals -->
    @include('livewire.pos-employees.components.employee-modal')
    @include('livewire.pos-employees.components.delete-modal')
</div>
