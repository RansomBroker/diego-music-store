<!-- MODAL FORM ATURAN DENDA PELANGGARAN -->
<x-pos.modal
    wire:model="showRuleModal"
    :title="$editingRuleId ? 'Edit Aturan Denda Presensi' : 'Tambah Aturan Denda Presensi Baru'"
    subtitle="Atur ambang batas menit, tipe potongan, dan nominal denda presensi karyawan"
    icon="ph-warning-circle"
    maxWidth="lg"
>
    <form wire:submit="saveRule" class="space-y-4">
        <!-- Nama Aturan -->
        <x-pos.form.input
            label="Nama Aturan Pelanggaran"
            model="ruleName"
            placeholder="Contoh: Terlambat Masuk 1-15 Menit"
            required
        />

        <!-- Jenis Pelanggaran -->
        <x-pos.form.dropdown
            label="Jenis Pelanggaran"
            model="violationType"
            required
        >
            <option value="late_in">Keterlambatan (Late In)</option>
            <option value="early_out">Pulang Cepat (Early Out)</option>
            <option value="unexcused_absence">Mangkir / Absen Tanpa Keterangan</option>
            <option value="leave_over_quota">Izin / Off Day Melampaui Kuota</option>
        </x-pos.form.dropdown>

        <!-- Batas Menit (Min - Max) -->
        <div class="grid grid-cols-2 gap-3">
            <x-pos.form.input
                label="Min. Menit Terlambat"
                type="number"
                min="0"
                model="minMinutes"
                suffix="Menit"
                required
            />
            <x-pos.form.input
                label="Max. Menit (Opsional)"
                type="number"
                model="maxMinutes"
                suffix="Menit"
                placeholder="Tanpa batas"
            />
        </div>

        <!-- Tipe Denda & Nominal -->
        <div class="grid grid-cols-2 gap-3">
            <x-pos.form.dropdown
                label="Tipe Denda / Potongan"
                model="deductionType"
                required
            >
                <option value="fixed_amount">Nominal Flat (Rp)</option>
                <option value="percentage_per_minute">Nominal Per Menit (Rp/menit)</option>
                <option value="percentage_daily_salary">Persentase Gaji Harian (%)</option>
            </x-pos.form.dropdown>
            <x-pos.form.input
                label="Nominal Denda (Rp)"
                :currency="true"
                model="deductionAmount"
                placeholder="0"
                required
            />
        </div>

        <!-- Status Aktif -->
        <x-pos.form.toggle
            label="Status Aturan"
            sublabel="Aktifkan aturan denda presensi ini"
            model="isActive"
        />

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 dark:border-slate-800">
            <x-pos.utility.button
                type="button"
                variant="secondary"
                size="sm"
                wire:click="$set('showRuleModal', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="submit"
                variant="primary"
                size="sm"
                icon="ph-check"
            >
                Simpan Aturan
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
