<!-- MODAL FORM TEMPLATE KPI -->
@if ($showTemplateModal)
    <div 
        class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4 overflow-y-auto"
        wire:click.self="$set('showTemplateModal', false)"
    >
        <div 
            x-data="{
                wSales: @entangle('weightSales').live,
                wAtv: @entangle('weightAtv').live,
                wAtt: @entangle('weightAttendance').live,
                wPunc: @entangle('weightPunctuality').live,
                get totalWeight() {
                    return (parseFloat(this.wSales) || 0) + 
                           (parseFloat(this.wAtv) || 0) + 
                           (parseFloat(this.wAtt) || 0) + 
                           (parseFloat(this.wPunc) || 0);
                }
            }"
            class="bg-white dark:bg-slate-900 rounded-3xl max-w-2xl w-full p-6 sm:p-7 shadow-2xl border border-slate-200 dark:border-slate-800 space-y-5 max-h-[90vh] overflow-y-auto no-scrollbar transition-colors"
        >
            <!-- Modal Header -->
            <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-800 pb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center">
                        <i class="ph-bold ph-sliders-horizontal text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-slate-900 dark:text-slate-100 tracking-tight">
                            {{ $editingTemplateId ? 'Edit Template KPI & Bonus' : 'Tambah Template KPI Baru' }}
                        </h3>
                        <p class="text-xs text-slate-400 dark:text-slate-500">
                            Atur target 4 indikator performa, bobot persentase, dan nominal insentif
                        </p>
                    </div>
                </div>
                <button 
                    type="button" 
                    wire:click="$set('showTemplateModal', false)" 
                    class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center transition cursor-pointer"
                >
                    <i class="ph-bold ph-x text-sm"></i>
                </button>
            </div>

            <!-- Form -->
            <form wire:submit="saveTemplate" class="space-y-5">
                <!-- Row 1: Nama Template & Peruntukan Jabatan -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-pos.form.input
                        label="Nama Template KPI"
                        model="templateName"
                        :required="true"
                        placeholder="Contoh: KPI Sales Executive 2026"
                        size="sm"
                        icon="ph-text-t"
                    />

                    <x-pos.form.input
                        label="Peruntukan Jabatan (Position)"
                        model="templatePosition"
                        placeholder="Contoh: Sales Executive / Cashier"
                        size="sm"
                        icon="ph-identification-badge"
                    />
                </div>

                <!-- Row 2: Target Karyawan Spesifik (Opsional) & Maksimum Bonus -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <x-pos.form.dropdown
                        label="Karyawan Spesifik (Opsional Override)"
                        model="templateEmployeeId"
                        size="sm"
                        icon="ph-user"
                    >
                        <option value="">Semua Karyawan Jabatan Ini</option>
                        @foreach ($employees as $emp)
                            <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->nik ?? '-' }})</option>
                        @endforeach
                    </x-pos.form.dropdown>

                    <x-pos.form.input
                        label="Maksimum Nominal Bonus Insentif"
                        type="currency"
                        model="maxBonusAmount"
                        :live="true"
                        :required="true"
                        size="sm"
                        placeholder="0"
                    />
                </div>

                <!-- 4 Indikator Utama & Bobot Persentase (%) Card -->
                <div class="p-5 bg-slate-50/80 dark:bg-slate-950/60 rounded-2xl border border-slate-200/80 dark:border-slate-800 space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 border-b border-slate-200/60 dark:border-slate-800/80 pb-3">
                        <div>
                            <h4 class="text-xs font-bold text-slate-800 dark:text-slate-200">
                                Pengaturan 4 Indikator Utama & Bobot Persentase (%)
                            </h4>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500 mt-0.5">
                                Target nominal otomatis diformat Rupiah dan bobot dihitung persentase
                            </p>
                        </div>
                        <!-- Live Total Weight Indicator -->
                        <div class="flex items-center gap-1.5 text-xs">
                            <span class="text-slate-500 dark:text-slate-400 font-medium">Total Bobot:</span>
                            <span 
                                :class="totalWeight === 100 ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/70 dark:text-emerald-400' : 'bg-rose-100 text-rose-700 dark:bg-rose-950/70 dark:text-rose-400'"
                                class="px-2 py-0.5 rounded-full  font-semibold"
                                x-text="totalWeight + '%'"
                            >100%</span>
                        </div>
                    </div>

                    <!-- 1. Target Sales -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <x-pos.form.input
                            label="Target Omset Penjualan (Rp)"
                            type="currency"
                            model="targetSalesAmount"
                            :live="true"
                            size="sm"
                            placeholder="0"
                        />
                        <x-pos.form.input
                            label="Bobot Sales (%)"
                            type="number"
                            step="0.1"
                            suffix="%"
                            model="weightSales"
                            :live="true"
                            size="sm"
                            placeholder="40"
                        />
                    </div>

                    <!-- 2. Target ATV -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <x-pos.form.input
                            label="Target ATV / Rata² Transaksi (Rp)"
                            type="currency"
                            model="targetAtvAmount"
                            :live="true"
                            size="sm"
                            placeholder="0"
                        />
                        <x-pos.form.input
                            label="Bobot ATV (%)"
                            type="number"
                            step="0.1"
                            suffix="%"
                            model="weightAtv"
                            :live="true"
                            size="sm"
                            placeholder="20"
                        />
                    </div>

                    <!-- 3. Target Absensi -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <x-pos.form.input
                            label="Target Kehadiran Absensi (%)"
                            type="number"
                            step="0.1"
                            suffix="%"
                            model="targetAttendancePct"
                            :live="true"
                            size="sm"
                            placeholder="95"
                        />
                        <x-pos.form.input
                            label="Bobot Absensi (%)"
                            type="number"
                            step="0.1"
                            suffix="%"
                            model="weightAttendance"
                            :live="true"
                            size="sm"
                            placeholder="20"
                        />
                    </div>

                    <!-- 4. Target Ketepatan Waktu -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <x-pos.form.input
                            label="Target Ketepatan Waktu (%)"
                            type="number"
                            step="0.1"
                            suffix="%"
                            model="targetPunctualityPct"
                            :live="true"
                            size="sm"
                            placeholder="95"
                        />
                        <x-pos.form.input
                            label="Bobot Ketepatan Waktu (%)"
                            type="number"
                            step="0.1"
                            suffix="%"
                            model="weightPunctuality"
                            :live="true"
                            size="sm"
                            placeholder="20"
                        />
                    </div>
                </div>

                <!-- Status Aktif Toggle -->
                <x-pos.form.toggle
                    label="Aktifkan Template KPI Ini"
                    sublabel="Template yang aktif dapat dipilih dan digunakan untuk evaluasi performa bulanan"
                    model="isActiveTemplate"
                />

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-800">
                    <x-pos.utility.button
                        type="button"
                        variant="secondary"
                        size="sm"
                        wire:click="$set('showTemplateModal', false)"
                    >
                        Batal
                    </x-pos.utility.button>

                    <x-pos.utility.button
                        type="submit"
                        variant="primary"
                        size="sm"
                        icon="ph-floppy-disk"
                    >
                        Simpan Template
                    </x-pos.utility.button>
                </div>
            </form>
        </div>
    </div>
@endif
