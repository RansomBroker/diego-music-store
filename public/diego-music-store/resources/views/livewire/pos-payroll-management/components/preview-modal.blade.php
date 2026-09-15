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
                        <x-pos.table.footer :total="count($previewItem->overtime_details)" />
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
                <x-pos.utility.button
                    type="button"
                    variant="secondary"
                    wire:click="closePreviewModal"
                >
                    Tutup
                </x-pos.utility.button>
                <x-pos.utility.button
                    variant="primary"
                    href="{{ route('pos.payroll.payslip-pdf', $previewItem->id) }}"
                    target="_blank"
                    icon="ph-printer"
                >
                    Cetak Slip PDF
                </x-pos.utility.button>
            </div>
        </div>
    </div>
@endif
