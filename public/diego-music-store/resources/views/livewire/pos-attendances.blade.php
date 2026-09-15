<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Toast Notification Listener -->
    <x-pos.toast />

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Presensi Karyawan"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header & Quick Actions -->
                @include('livewire.pos-attendances.components.header-bar')

                <!-- Table Card Wrapper -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">

                    <!-- Navigation Tabs -->
                    <div class="flex border-b border-slate-200 dark:border-slate-800 overflow-x-auto no-scrollbar bg-slate-50/50 dark:bg-slate-900/50">
                        <button
                            type="button"
                            wire:click="$set('activeTab', 'attendances')"
                            class="px-6 py-3.5 text-xs font-black uppercase tracking-wider transition border-b-2 flex items-center gap-2 whitespace-nowrap cursor-pointer {{ $activeTab === 'attendances' ? 'border-primary text-primary dark:text-blue-400 bg-white dark:bg-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
                        >
                            <i class="ph-bold ph-calendar-check text-base"></i>
                            <span>Riwayat Presensi Karyawan</span>
                        </button>

                        <button
                            type="button"
                            wire:click="$set('activeTab', 'backdate_requests')"
                            class="px-6 py-3.5 text-xs font-black uppercase tracking-wider transition border-b-2 flex items-center gap-2 whitespace-nowrap cursor-pointer {{ $activeTab === 'backdate_requests' ? 'border-primary text-primary dark:text-blue-400 bg-white dark:bg-slate-900' : 'border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-300' }}"
                        >
                            <i class="ph-bold ph-hourglass text-base"></i>
                            <span>Persetujuan Request Backdate</span>
                            @if (count($pendingBackdateRequests) > 0)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-amber-500 text-white ml-1">
                                    {{ count($pendingBackdateRequests) }}
                                </span>
                            @endif
                        </button>
                    </div>

                    @if ($activeTab === 'attendances')
                        @include('livewire.pos-attendances.components.attendances-table')
                    @else
                        @include('livewire.pos-attendances.components.backdate-requests-table')
                    @endif

                </div>
            </div>
        </div>
    </main>

    <!-- Modals -->
    @include('livewire.pos-attendances.components.clock-modal')
    @include('livewire.pos-attendances.components.record-modal')
    @include('livewire.pos-attendances.components.backdate-modal')
</div>
