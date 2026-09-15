<div class="flex h-screen min-h-dvh max-h-dvh w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- POS Global Toast Notification Listener -->
    <x-pos.toast />

    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden">
        <!-- Header POS Global Navbar -->
        <x-pos.navbar
            pageTitle="Dashboard"
            backLabel="Backoffice"
            backUrl="/backoffice"
            :activeSessionInfo="$activeSessionInfo"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 no-scrollbar">
            <div class="w-full space-y-6">

                @php
                    $isOwnerUser = auth()->check() && auth()->user()->hasRole(['owner', 'admin', 'super_admin', 'Owner', 'Admin', 'Super Admin']);
                @endphp

                <!-- 1. Card Status User Login & Presensi -->
                @include('livewire.front-office-dashboard.components.user-status-card')

                <!-- 2. Quick Navigation Tiles -->
                @include('livewire.front-office-dashboard.components.navigation-tiles')

                <!-- 3. Executive Owner Dashboard Widgets Section -->
                <livewire:owner-dashboard-widgets />

                <!-- 4. Sales & Employee Performance Dashboard Widgets Section -->
                <livewire:sales-employee-dashboard-widgets />

                <!-- 5. Presensi Karyawan Cabang Hari Ini (Owner Only) -->
                @if ($isOwnerUser)
                    @include('livewire.front-office-dashboard.components.today-branch-attendances')
                @endif

            </div>
        </div>
    </main>
</div>
