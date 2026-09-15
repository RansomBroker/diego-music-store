<!-- Page Header (Title & Breadcrumbs & Header Actions) -->
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
                        <span class="text-slate-400 dark:text-slate-500">Keuangan</span>
                    </div>
                </li>
                <li>
                    <div class="flex items-center">
                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                        <span class="text-slate-650 dark:text-slate-300 font-bold">Payroll & Gaji</span>
                    </div>
                </li>
            </ol>
        </nav>
        <!-- Page Title -->
        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Payroll & Gaji Karyawan</h1>
    </div>

    <!-- Header Actions -->
    <div class="flex flex-wrap items-center gap-2">
        <!-- Generate / Process Payroll Button -->
        <x-pos.utility.button
            type="button"
            variant="primary"
            size="sm"
            icon="ph-arrows-clockwise"
            wire:click="generatePayroll()"
        >
            Proses Payroll Bulanan
        </x-pos.utility.button>

        @if ($currentPayroll)
            <!-- Cetak Semua Slip Gaji (Bulk PDF) - Tampilan persis seperti tombol proses payroll bulanan -->
            <x-pos.utility.button
                href="{{ route('pos.payroll.bulk-payslip-pdf', $currentPayroll->id) }}"
                target="_blank"
                variant="primary"
                size="sm"
                icon="ph-printer"
            >
                Cetak Semua Slip (Bulk PDF)
            </x-pos.utility.button>

            <!-- Export Excel -->
            <x-pos.utility.button
                type="button"
                variant="success"
                size="sm"
                icon="ph-file-xls"
                wire:click="exportExcel({{ $currentPayroll->id }})"
            >
                Export Excel
            </x-pos.utility.button>

            <!-- Approve Payroll -->
            @if ($currentPayroll->status === 'draft')
                <x-pos.utility.button
                    type="button"
                    variant="primary"
                    size="sm"
                    icon="ph-seal-check"
                    wire:click="approvePayroll({{ $currentPayroll->id }})"
                >
                    Setujui (Approve)
                </x-pos.utility.button>
            @endif

            <!-- Process Payment -->
            @if ($currentPayroll->status !== 'paid' && $currentPayroll->status !== 'cancelled')
                <x-pos.utility.button
                    type="button"
                    variant="success"
                    size="sm"
                    icon="ph-check-circle"
                    wire:click="processPayment({{ $currentPayroll->id }})"
                    wire:confirm="Tandai payroll periode {{ $currentPayroll->period }} sebagai PAID (Telah Dibayar)?"
                >
                    Tandai Paid (Bayar)
                </x-pos.utility.button>

                <!-- Cancel Payroll -->
                <x-pos.utility.button
                    type="button"
                    variant="danger"
                    size="sm"
                    icon="ph-x-circle"
                    wire:click="cancelPayroll({{ $currentPayroll->id }})"
                    wire:confirm="Batalkan payroll periode {{ $currentPayroll->period }}? Anda dapat memproses ulang nanti."
                >
                    Batalkan (Cancel)
                </x-pos.utility.button>
            @endif
        @endif
    </div>
</div>
