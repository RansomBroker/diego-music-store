<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header POS Global -->
        <x-pos.navbar
            pageTitle="Payroll & Gaji Karyawan"
            backLabel="Dashboard"
            backUrl="/pos/front-office"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-6 md:p-8 no-scrollbar">
            <div class="w-full space-y-6">

                <!-- Top Header Filter & Action Bar -->
                <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 shadow-sm border border-slate-200/70 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3">
                            <div class="w-11 h-11 rounded-2xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-black text-2xl">
                                <i class="ph-bold ph-bank"></i>
                            </div>
                            <div>
                                <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 tracking-tight">Otomatisasi Payroll & Gaji</h2>
                                <p class="text-xs text-slate-400 dark:text-slate-500">Rekap Gaji Pokok, Tunjangan Lembur, Komisi, Bonus KPI, Potongan Presensi, & Slip PDF</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                        <!-- Filter Cabang -->
                        <div class="w-full sm:w-44">
                            <select
                                wire:model.live="filterBranchId"
                                class="w-full px-3.5 py-2 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:border-primary"
                            >
                                <option value="">Semua Cabang</option>
                                @foreach ($branches as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Filter Bulan -->
                        <div class="w-full sm:w-36">
                            <input
                                type="month"
                                wire:model.live="filterMonth"
                                class="w-full px-3.5 py-2 bg-slate-100 dark:bg-slate-700 border border-slate-200 dark:border-slate-600 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:outline-none focus:border-primary"
                            />
                        </div>

                        <!-- Generate / Process Payroll Button -->
                        <button
                            type="button"
                            wire:click="generatePayroll()"
                            class="px-4 py-2 bg-primary hover:bg-primaryDark text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                        >
                            <i class="ph-bold ph-arrows-clockwise text-base"></i>
                            <span>Proses Payroll Bulanan</span>
                        </button>

                        @if ($currentPayroll)
                            <!-- Cetak Semua Slip Gaji (Bulk PDF) -->
                            <a
                                href="{{ route('pos.payroll.bulk-payslip-pdf', $currentPayroll->id) }}"
                                target="_blank"
                                class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                            >
                                <i class="ph-bold ph-printer text-base"></i>
                                <span>Cetak Semua Slip (Bulk PDF)</span>
                            </a>

                            <!-- Export Excel -->
                            <button
                                type="button"
                                wire:click="exportExcel({{ $currentPayroll->id }})"
                                class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                            >
                                <i class="ph-bold ph-file-xls text-base"></i>
                                <span>Export Excel</span>
                            </button>

                            <!-- Approve Payroll -->
                            @if ($currentPayroll->status === 'draft')
                                <button
                                    type="button"
                                    wire:click="approvePayroll({{ $currentPayroll->id }})"
                                    class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                                >
                                    <i class="ph-bold ph-seal-check text-base"></i>
                                    <span>Setujui (Approve)</span>
                                </button>
                            @endif

                            <!-- Process Payment -->
                            @if ($currentPayroll->status !== 'paid' && $currentPayroll->status !== 'cancelled')
                                <button
                                    type="button"
                                    wire:click="processPayment({{ $currentPayroll->id }})"
                                    wire:confirm="Tandai payroll periode {{ $currentPayroll->period }} sebagai PAID (Telah Dibayar)?"
                                    class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                                >
                                    <i class="ph-bold ph-check-circle text-base"></i>
                                    <span>Tandai Paid (Bayar)</span>
                                </button>
                            @endif

                            <!-- Cancel Payroll -->
                            @if ($currentPayroll->status !== 'paid' && $currentPayroll->status !== 'cancelled')
                                <button
                                    type="button"
                                    wire:click="cancelPayroll({{ $currentPayroll->id }})"
                                    wire:confirm="Batalkan payroll periode {{ $currentPayroll->period }}? Anda dapat memproses ulang nanti."
                                    class="px-3.5 py-2 bg-rose-600 hover:bg-rose-700 text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-2 cursor-pointer"
                                >
                                    <i class="ph-bold ph-x-circle text-base"></i>
                                    <span>Batalkan (Cancel)</span>
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                @if ($currentPayroll)
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700/80 shadow-sm">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Staf Gaji</span>
                            <div class="text-xl font-black text-slate-900 dark:text-slate-100 mt-1">{{ $currentPayroll->total_employees }} Karyawan</div>
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700/80 shadow-sm">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Komisi & Bonus KPI</span>
                            <div class="text-xl font-black text-blue-600 dark:text-blue-400 mt-1">
                                Rp {{ number_format($currentPayroll->total_commissions + $currentPayroll->total_kpi_bonuses, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="bg-white dark:bg-slate-800 rounded-2xl p-4 border border-slate-200 dark:border-slate-700/80 shadow-sm">
                            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Total Potongan (Presensi+Lain)</span>
                            <div class="text-xl font-black text-rose-600 dark:text-rose-400 mt-1">
                                Rp {{ number_format($currentPayroll->total_deductions, 0, ',', '.') }}
                            </div>
                        </div>

                        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 rounded-2xl p-4 text-white shadow-lg shadow-emerald-500/20">
                            <span class="text-[11px] font-bold opacity-80 uppercase tracking-wider block">TOTAL GAJI BERSIH (NET)</span>
                            <div class="text-xl font-black mt-1">
                                Rp {{ number_format($currentPayroll->total_net_salary, 0, ',', '.') }}
                            </div>
                        </div>
                    </div>

                    <!-- Payroll Items Detail Table -->
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200 dark:border-slate-700/80 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <h3 class="text-sm font-black text-slate-900 dark:text-slate-100">
                                    Rincian Slip Gaji Karyawan (Kode: {{ $currentPayroll->payroll_code }})
                                </h3>
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-black {{ $currentPayroll->status === 'paid' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-950/70 dark:text-amber-400' }}">
                                    STATUS: {{ strtoupper($currentPayroll->status) }}
                                </span>
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-100 dark:bg-slate-900/50 text-[11px] font-black uppercase tracking-wider text-slate-600 dark:text-slate-400 border-b border-slate-200 dark:border-slate-800">
                                        <th class="py-3 px-4">Nama Karyawan</th>
                                        <th class="py-3 px-4 text-right">Gaji Pokok</th>
                                        <th class="py-3 px-4 text-right">Tunjangan</th>
                                        <th class="py-3 px-4 text-right">Tunj. Lembur</th>
                                        <th class="py-3 px-4 text-right">Komisi Sales</th>
                                        <th class="py-3 px-4 text-right">Bonus KPI</th>
                                        <th class="py-3 px-4 text-right">Denda Presensi</th>
                                        <th class="py-3 px-4 text-right">Potongan Lain</th>
                                        <th class="py-3 px-4 text-right">Gaji Bersih</th>
                                        <th class="py-3 px-4 text-center">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800 text-xs">
                                    @foreach ($currentPayroll->items as $item)
                                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30 transition">
                                            <td class="py-3 px-4 font-bold text-slate-900 dark:text-slate-100">
                                                {{ $item->employee->name ?? '-' }}
                                                <span class="block text-[10px] text-slate-400 font-normal">{{ $item->employee->nik ?? '' }}</span>
                                            </td>
                                            <td class="py-3 px-4 text-right font-semibold text-slate-800 dark:text-slate-200">
                                                Rp {{ number_format($item->basic_salary, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-medium text-emerald-600 dark:text-emerald-400">
                                                Rp {{ number_format($item->allowance_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-medium text-sky-600 dark:text-sky-400">
                                                Rp {{ number_format($item->overtime_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-medium text-blue-600 dark:text-blue-400">
                                                Rp {{ number_format($item->commission_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-medium text-purple-600 dark:text-purple-400">
                                                Rp {{ number_format($item->kpi_bonus_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-medium text-rose-600 dark:text-rose-400">
                                                Rp {{ number_format($item->violation_deduction_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-medium text-amber-600 dark:text-amber-400">
                                                Rp {{ number_format($item->other_deduction_amount, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-right font-black text-emerald-700 dark:text-emerald-300">
                                                Rp {{ number_format($item->net_salary, 0, ',', '.') }}
                                            </td>
                                            <td class="py-3 px-4 text-center space-x-1">
                                                <!-- Preview Modal Button -->
                                                <button
                                                    type="button"
                                                    wire:click="openPreviewModal({{ $item->id }})"
                                                    class="px-2 py-1 bg-sky-50 hover:bg-sky-100 dark:bg-sky-900/40 text-sky-700 dark:text-sky-300 font-bold text-[11px] rounded-lg transition inline-flex items-center gap-1 cursor-pointer"
                                                    title="Preview Slip & Detail Lembur"
                                                >
                                                    <i class="ph-bold ph-eye"></i> Detail
                                                </button>

                                                <!-- Edit Tunjangan & Potongan -->
                                                <button
                                                    type="button"
                                                    wire:click="openEditItemModal({{ $item->id }})"
                                                    class="px-2 py-1 bg-slate-100 hover:bg-slate-200 dark:bg-slate-700 text-slate-800 dark:text-slate-200 font-bold text-[11px] rounded-lg transition cursor-pointer"
                                                    title="Edit Tunjangan & Potongan Manual"
                                                >
                                                    <i class="ph-bold ph-pencil-simple"></i>
                                                </button>

                                                <!-- Cetak Slip Gaji PDF -->
                                                <a
                                                    href="{{ route('pos.payroll.payslip-pdf', $item->id) }}"
                                                    target="_blank"
                                                    class="px-2 py-1 bg-primary hover:bg-primaryDark text-white font-bold text-[11px] rounded-lg transition inline-flex items-center gap-1 cursor-pointer"
                                                    title="Cetak Slip Gaji PDF"
                                                >
                                                    <i class="ph-bold ph-printer"></i> Slip PDF
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @else
                    <div class="bg-white dark:bg-slate-800 rounded-3xl p-12 text-center text-slate-400 border border-slate-200 dark:border-slate-700/80">
                        <i class="ph-bold ph-bank text-4xl mb-2 text-slate-300"></i>
                        <p class="text-sm font-bold mb-3">Belum ada rekapitulasi payroll untuk periode {{ $filterMonth }}.</p>
                        <button
                            type="button"
                            wire:click="generatePayroll()"
                            class="px-5 py-2.5 bg-primary hover:bg-primaryDark text-white font-extrabold text-xs rounded-xl shadow transition cursor-pointer"
                        >
                            Proses Payroll Sekarang
                        </button>
                    </div>
                @endif

            </div>
        </div>
    </main>

    <!-- MODAL PREVIEW DETAIL SLIP & LEMBUR -->
    @if ($showPreviewModal && $previewItem)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <div>
                        <h3 class="text-base font-black text-slate-900 dark:text-slate-100">
                            Rincian Slip Gaji - {{ $previewItem->employee->name ?? 'Karyawan' }}
                        </h3>
                        <p class="text-xs text-slate-400">
                            NIK: {{ $previewItem->employee->nik ?? '-' }} • Periode: {{ $previewItem->payroll->period ?? '' }}
                        </p>
                    </div>
                    <button type="button" wire:click="closePreviewModal" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </div>

                <!-- Summary Ringkasan Pendapatan & Potongan -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 p-4 rounded-2xl border border-emerald-100 dark:border-emerald-800/50 space-y-1">
                        <span class="text-[11px] font-extrabold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider block">Total Pendapatan</span>
                        <div class="text-lg font-black text-emerald-800 dark:text-emerald-300">
                            Rp {{ number_format($previewItem->basic_salary + $previewItem->allowance_amount + $previewItem->overtime_amount + $previewItem->commission_amount + $previewItem->kpi_bonus_amount, 0, ',', '.') }}
                        </div>
                        <ul class="text-xs text-slate-600 dark:text-slate-400 space-y-0.5 pt-2 border-t border-emerald-200/60 dark:border-emerald-800">
                            <li>• Gaji Pokok: Rp {{ number_format($previewItem->basic_salary, 0, ',', '.') }}</li>
                            <li>• Tunjangan Tetap: Rp {{ number_format($previewItem->allowance_amount, 0, ',', '.') }}</li>
                            <li>• Tunjangan Lembur: Rp {{ number_format($previewItem->overtime_amount, 0, ',', '.') }}</li>
                            <li>• Komisi Sales: Rp {{ number_format($previewItem->commission_amount, 0, ',', '.') }}</li>
                            <li>• Bonus KPI: Rp {{ number_format($previewItem->kpi_bonus_amount, 0, ',', '.') }}</li>
                        </ul>
                    </div>

                    <div class="bg-rose-50 dark:bg-rose-950/40 p-4 rounded-2xl border border-rose-100 dark:border-rose-800/50 space-y-1">
                        <span class="text-[11px] font-extrabold text-rose-700 dark:text-rose-400 uppercase tracking-wider block">Total Potongan</span>
                        <div class="text-lg font-black text-rose-800 dark:text-rose-300">
                            Rp {{ number_format($previewItem->violation_deduction_amount + $previewItem->other_deduction_amount, 0, ',', '.') }}
                        </div>
                        <ul class="text-xs text-slate-600 dark:text-slate-400 space-y-0.5 pt-2 border-t border-rose-200/60 dark:border-rose-800">
                            <li>• Denda Presensi: Rp {{ number_format($previewItem->violation_deduction_amount, 0, ',', '.') }}</li>
                            <li>• Potongan Lain/Kasbon: Rp {{ number_format($previewItem->other_deduction_amount, 0, ',', '.') }}</li>
                        </ul>
                    </div>
                </div>

                <!-- Rincian Jam Kerja Lembur -->
                <div class="space-y-2 pt-2">
                    <h4 class="text-xs font-black text-slate-900 dark:text-slate-100 uppercase tracking-wider">
                        Rincian Jam Kerja Lembur (Approved Overtime)
                    </h4>
                    @if (!empty($previewItem->overtime_details) && count($previewItem->overtime_details) > 0)
                        <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                            <table class="w-full text-left text-xs">
                                <thead class="bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400 font-bold uppercase">
                                    <tr>
                                        <th class="py-2 px-3">Tanggal</th>
                                        <th class="py-2 px-3">Jam Mulai</th>
                                        <th class="py-2 px-3">Jam Selesai</th>
                                        <th class="py-2 px-3 text-center">Durasi</th>
                                        <th class="py-2 px-3 text-center">Status</th>
                                        <th class="py-2 px-3 text-right">Upah Lembur</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                                    @foreach ($previewItem->overtime_details as $ot)
                                        <tr>
                                            <td class="py-2 px-3">{{ $ot['date'] ?? '-' }}</td>
                                            <td class="py-2 px-3 font-semibold">{{ $ot['start_time'] ?? '-' }}</td>
                                            <td class="py-2 px-3 font-semibold">{{ $ot['end_time'] ?? '-' }}</td>
                                            <td class="py-2 px-3 text-center font-bold">{{ number_format($ot['hours'] ?? 0, 1) }} jam</td>
                                            <td class="py-2 px-3 text-center">
                                                <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                                    {{ strtoupper($ot['status'] ?? 'APPROVED') }}
                                                </span>
                                            </td>
                                            <td class="py-2 px-3 text-right font-black text-emerald-600">
                                                Rp {{ number_format($ot['overtime_amount'] ?? 0, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-3 bg-slate-50 dark:bg-slate-800/50 rounded-xl text-xs text-slate-400 text-center">
                            Tidak ada pengajuan lembur yang disetujui untuk karyawan ini pada periode {{ $previewItem->payroll->period ?? '' }}.
                        </div>
                    @endif
                </div>

                <!-- Total Take Home Pay -->
                <div class="bg-gradient-to-r from-emerald-600 to-teal-700 rounded-2xl p-4 text-white flex items-center justify-between">
                    <div>
                        <span class="text-xs font-bold opacity-80 uppercase tracking-wider block">Gaji Bersih Diterima (Take Home Pay)</span>
                        <span class="text-xs opacity-75">Rekening: {{ $previewItem->bank_name ?? 'BCA' }} {{ $previewItem->bank_account_number ?? '-' }} (a.n {{ $previewItem->bank_account_holder ?? '-' }})</span>
                    </div>
                    <div class="text-2xl font-black">
                        Rp {{ number_format($previewItem->net_salary, 0, ',', '.') }}
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button
                        type="button"
                        wire:click="closePreviewModal"
                        class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl transition cursor-pointer"
                    >
                        Tutup
                    </button>
                    <a
                        href="{{ route('pos.payroll.payslip-pdf', $previewItem->id) }}"
                        target="_blank"
                        class="px-4 py-2 bg-primary hover:bg-primaryDark text-white font-extrabold text-xs rounded-xl shadow-sm transition flex items-center gap-1 cursor-pointer"
                    >
                        <i class="ph-bold ph-printer"></i> Cetak Slip PDF
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- MODAL EDIT TUNJANGAN & POTONGAN MANUAL -->
    @if ($showEditItemModal)
        <div class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
            <div class="bg-white dark:bg-slate-900 rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3">
                    <h3 class="text-base font-black text-slate-900 dark:text-slate-100">
                        Edit Tunjangan & Potongan Karyawan
                    </h3>
                    <button type="button" wire:click="$set('showEditItemModal', false)" class="text-slate-400 hover:text-slate-600 text-lg cursor-pointer">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </div>

                <form wire:submit="saveItemDetails" class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Tunjangan Tetap / Tambahan (Rp)
                        </label>
                        <input
                            type="number"
                            step="1000"
                            wire:model="editAllowanceAmount"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Potongan Lainnya / Kasbon (Rp)
                        </label>
                        <input
                            type="number"
                            step="1000"
                            wire:model="editOtherDeductionAmount"
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                            Catatan Keterangan
                        </label>
                        <textarea
                            wire:model="editNotes"
                            rows="2"
                            placeholder="Catatan penyesuaian tunjangan/potongan..."
                            class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                        ></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                        <button
                            type="button"
                            wire:click="$set('showEditItemModal', false)"
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-800 text-slate-700 dark:text-slate-300 font-extrabold text-xs rounded-xl transition cursor-pointer"
                        >
                            Batal
                        </button>
                        <button
                            type="submit"
                            class="px-5 py-2 bg-primary hover:bg-primaryDark text-white font-extrabold text-xs rounded-xl shadow-sm transition cursor-pointer"
                        >
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
