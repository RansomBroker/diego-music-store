<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200" x-data="{ 
    openModal: @entangle('showSupervisorModal') 
}" @print-z-report.window="window.open($event.detail[0].url, '_blank', 'width=400,height=600,menubar=no,toolbar=no,location=no,status=no')">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Toast Notification Listener -->
        <x-pos.toast />

        <!-- Header — menggunakan komponen navbar POS global -->
        <x-pos.navbar
            pageTitle="Sesi Kasir"
            backLabel="Dashboard"
            :activeSessionInfo="$activeSession ? [
                'id'           => $activeSession->id,
                'opened_at'    => $activeSession->opened_at->format('d M Y H:i'),
                'opening_cash' => $activeSession->opening_cash,
            ] : null"
        >
            {{-- Tombol Kembali ke POS Kasir (hanya tampil jika sesi aktif) --}}
            @if ($activeSession)
                <a href="/pos" class="flex items-center gap-2 px-4 h-11 bg-primary hover:bg-primaryDark text-white font-semibold text-sm rounded-xl shadow-md shadow-primary/20 transition-all">
                    <i class="ph-bold ph-squares-four text-lg"></i>
                    <span>Ke POS Kasir</span>
                </a>
            @endif
        </x-pos.navbar>

        <!-- Tabs Sub-navigation -->
        @include('livewire.pos-cash-session.components.tabs-navigation')

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-8 no-scrollbar">
            @if ($activeTab === 'sesi')
                @if ($activeSession)
                    @include('livewire.pos-cash-session.components.active-session-view')
                @else
                    @include('livewire.pos-cash-session.components.open-session-form')
                @endif
            @else
                @include('livewire.pos-cash-session.components.history-table')
            @endif
        </div>
    </main>

    <!-- ================= SUPERVISOR APPROVAL MODAL ================= -->
    @include('livewire.pos-cash-session.components.supervisor-modal')

    <!-- ================= TRANSACTIONS DETAILS MODAL ================= -->
    @include('livewire.pos-cash-session.components.transactions-modal')
</div>
