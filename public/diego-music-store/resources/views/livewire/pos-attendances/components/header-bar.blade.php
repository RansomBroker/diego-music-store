<!-- Page Header & Quick Actions -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <!-- Breadcrumbs -->
        <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                <li class="inline-flex items-center">
                    <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                        <span class="text-slate-650 dark:text-slate-300 font-bold">Presensi Karyawan</span>
                    </div>
                </li>
            </ol>
        </nav>
        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Presensi & Off Day Karyawan</h1>
    </div>

    <!-- Quick Clock-In / Clock-Out & Backdate Buttons -->
    <div class="flex flex-wrap items-center gap-2">
        <x-pos.utility.button
            variant="success"
            size="sm"
            icon="ph-sign-in"
            wire:click="openClockModal('in')"
        >
            Clock In (Masuk)
        </x-pos.utility.button>

        <x-pos.utility.button
            variant="warning"
            size="sm"
            icon="ph-sign-out"
            wire:click="openClockModal('out')"
        >
            Clock Out (Pulang)
        </x-pos.utility.button>

        <x-pos.utility.button
            variant="secondary"
            size="sm"
            icon="ph-clock-counter-clockwise"
            wire:click="openBackdateModal()"
            title="Ajukan presensi susulan untuk tanggal lampau"
        >
            Request Backdate
        </x-pos.utility.button>

        <x-pos.utility.button
            variant="primary"
            size="sm"
            icon="ph-calendar-plus"
            wire:click="openRecordModal()"
        >
            Catat Off Day / Izin
        </x-pos.utility.button>
    </div>
</div>
