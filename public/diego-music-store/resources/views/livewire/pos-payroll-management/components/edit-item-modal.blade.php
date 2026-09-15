<!-- MODAL EDIT / OVERRIDE KOMPONEN GAJI KARYAWAN -->
@if ($showEditItemModal)
    <div 
        class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
        wire:click.self="$set('showEditItemModal', false)"
    >
        <div 
            x-data="{
                basicSalary: @entangle('editBasicSalary').live,
                allowanceAmount: @entangle('editAllowanceAmount').live,
                overtimeAmount: @entangle('editOvertimeAmount').live,
                commissionAmount: @entangle('editCommissionAmount').live,
                kpiBonusAmount: @entangle('editKpiBonusAmount').live,
                violationAmount: @entangle('editViolationDeductionAmount').live,
                otherDeductionAmount: @entangle('editOtherDeductionAmount').live,
                formatRp(val) {
                    return 'Rp ' + Math.max(0, Math.round(val || 0)).toLocaleString('id-ID');
                },
                get totalEarnings() {
                    return (parseFloat(this.basicSalary) || 0) + 
                           (parseFloat(this.allowanceAmount) || 0) + 
                           (parseFloat(this.overtimeAmount) || 0) + 
                           (parseFloat(this.commissionAmount) || 0) + 
                           (parseFloat(this.kpiBonusAmount) || 0);
                },
                get totalDeductions() {
                    return (parseFloat(this.violationAmount) || 0) + 
                           (parseFloat(this.otherDeductionAmount) || 0);
                },
                get netSalary() {
                    return Math.max(0, this.totalEarnings - this.totalDeductions);
                }
            }"
            class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5 max-h-[90vh] overflow-y-auto no-scrollbar"
        >
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-3.5">
                <div>
                    <h3 class="text-base font-black text-slate-900 dark:text-slate-100 tracking-tight">
                        Override Komponen Gaji Karyawan
                    </h3>
                    <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-0.5">
                        {{ $editingEmployeeName }} <span class="font-mono text-primary font-bold">({{ $editingEmployeeNik ?: '-' }})</span>
                    </p>
                </div>
                <button 
                    type="button" 
                    wire:click="$set('showEditItemModal', false)" 
                    class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-white flex items-center justify-center transition cursor-pointer"
                >
                    <i class="ph-bold ph-x text-base"></i>
                </button>
            </div>

            <!-- Form -->
            <form wire:submit="saveItemDetails" novalidate class="space-y-5">
                
                <!-- Section 1: Pendapatan & Tunjangan -->
                <div class="bg-emerald-50/40 dark:bg-emerald-950/20 rounded-2xl p-4 border border-emerald-100 dark:border-emerald-900/40 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-emerald-800 dark:text-emerald-300 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ph-bold ph-trend-up text-emerald-600"></i>
                            Pendapatan & Tunjangan (Penambahan)
                        </span>
                        <span class="text-xs font-black text-emerald-700 dark:text-emerald-400 font-mono" x-text="formatRp(totalEarnings)"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Gaji Pokok -->
                        <x-pos.form.input
                            label="Gaji Pokok"
                            type="currency"
                            model="editBasicSalary"
                            :live="true"
                            size="sm"
                            placeholder="0"
                        />

                        <!-- Tunjangan Tetap / Tambahan -->
                        <x-pos.form.input
                            label="Tunjangan Tetap / Tambahan"
                            type="currency"
                            model="editAllowanceAmount"
                            :live="true"
                            size="sm"
                            placeholder="0"
                        />

                        <!-- Tunjangan Lembur -->
                        <x-pos.form.input
                            label="Tunjangan Lembur"
                            type="currency"
                            model="editOvertimeAmount"
                            :live="true"
                            size="sm"
                            placeholder="0"
                        />

                        <!-- Komisi Sales -->
                        <x-pos.form.input
                            label="Komisi Penjualan Sales"
                            type="currency"
                            model="editCommissionAmount"
                            :live="true"
                            size="sm"
                            placeholder="0"
                        />

                        <!-- Bonus KPI -->
                        <div class="sm:col-span-2">
                            <x-pos.form.input
                                label="Bonus Pencapaian KPI"
                                type="currency"
                                model="editKpiBonusAmount"
                                :live="true"
                                size="sm"
                                placeholder="0"
                            />
                        </div>
                    </div>
                </div>

                <!-- Section 2: Potongan & Denda -->
                <div class="bg-rose-50/40 dark:bg-rose-950/20 rounded-2xl p-4 border border-rose-100 dark:border-rose-900/40 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-rose-800 dark:text-rose-300 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="ph-bold ph-trend-down text-rose-600"></i>
                            Potongan & Denda (Pengurangan)
                        </span>
                        <span class="text-xs font-black text-rose-700 dark:text-rose-400 font-mono" x-text="'- ' + formatRp(totalDeductions)"></span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Denda Presensi -->
                        <x-pos.form.input
                            label="Denda Presensi & Keterlambatan"
                            type="currency"
                            model="editViolationDeductionAmount"
                            :live="true"
                            size="sm"
                            placeholder="0"
                        />

                        <!-- Potongan Lainnya / Kasbon -->
                        <x-pos.form.input
                            label="Potongan Lainnya / Kasbon"
                            type="currency"
                            model="editOtherDeductionAmount"
                            :live="true"
                            size="sm"
                            placeholder="0"
                        />
                    </div>
                </div>

                <!-- Section 3: Catatan Penyesuaian -->
                <div>
                    <label class="block text-[11px] font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Catatan Keterangan Penyesuaian
                    </label>
                    <textarea
                        wire:model="editNotes"
                        rows="2"
                        placeholder="Contoh: Penyesuaian bonus lembur proyek khusus atau pelunasan kasbon..."
                        class="w-full px-3 py-2 bg-slate-50 dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                    ></textarea>
                </div>

                <!-- Live Net Salary Summary Banner -->
                <div class="p-4 bg-emerald-50 dark:bg-emerald-950/40 border border-emerald-200 dark:border-emerald-800/60 rounded-2xl flex items-center justify-between shadow-sm transition-colors">
                    <div>
                        <div class="text-[10px] uppercase font-bold text-emerald-800 dark:text-emerald-300 tracking-wider">Estimasi Gaji Bersih (Take Home Pay)</div>
                        <div class="text-lg sm:text-xl font-black text-emerald-700 dark:text-emerald-400 font-mono tracking-tight" x-text="formatRp(netSalary)"></div>
                    </div>
                    <div class="text-right text-[11px] text-slate-600 dark:text-slate-400 space-y-0.5">
                        <div>Pendapatan: <span class="text-slate-900 dark:text-slate-100 font-bold font-mono" x-text="formatRp(totalEarnings)"></span></div>
                        <div>Potongan: <span class="text-rose-600 dark:text-rose-400 font-bold font-mono" x-text="'- ' + formatRp(totalDeductions)"></span></div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <x-pos.utility.button
                        type="button"
                        variant="secondary"
                        size="sm"
                        wire:click="$set('showEditItemModal', false)"
                    >
                        Batal
                    </x-pos.utility.button>

                    <x-pos.utility.button
                        type="submit"
                        variant="primary"
                        size="sm"
                        icon="ph-check"
                    >
                        Simpan & Override Gaji
                    </x-pos.utility.button>
                </div>
            </form>
        </div>
    </div>
@endif
