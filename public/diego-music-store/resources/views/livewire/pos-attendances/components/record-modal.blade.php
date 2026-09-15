<!-- Modal Catat Status Presensi / Off Day -->
<x-pos.modal
    wire:model="showModal"
    title="Catat Status Presensi / Off Day"
    subtitle="Pilih karyawan, tanggal, dan status presensi yang diajukan"
    icon="ph-calendar-plus"
    maxWidth="md"
>
    <form wire:submit.prevent="saveRecord" class="space-y-4">
        <!-- Pilih Karyawan -->
        <x-pos.form.dropdown
            label="Pilih Karyawan"
            model="selectedEmployeeId"
            icon="ph-user"
            placeholder="-- Pilih Karyawan --"
            required
        >
            @foreach ($employees as $e)
                <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->nik }})</option>
            @endforeach
        </x-pos.form.dropdown>

        <!-- Tanggal -->
        <x-pos.form.input
            label="Tanggal Presensi"
            type="date"
            model="attendanceDate"
            icon="ph-calendar"
            required
        />

        <!-- Status Presensi -->
        <x-pos.form.dropdown
            label="Status Presensi"
            model="status"
            icon="ph-check-circle"
            required
        >
            <option value="off_day">Off Day (Hari Libur Jatah)</option>
            <option value="hadir">Hadir</option>
            <option value="izin">Izin</option>
            <option value="sakit">Sakit</option>
            <option value="alpha">Alpha (Tanpa Keterangan)</option>
        </x-pos.form.dropdown>

        <!-- Catatan -->
        <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1.5">
                Catatan / Alasan (Opsional)
            </label>
            <textarea
                wire:model="notes"
                rows="2"
                placeholder="Isikan keterangan..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl outline-hidden font-bold text-sm focus:ring-2 focus:ring-primary dark:focus:ring-blue-500 text-slate-800 dark:text-slate-100"
            ></textarea>
            @error('notes') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                variant="secondary"
                size="sm"
                wire:click="$set('showModal', false)"
            >
                Batal
            </x-pos.utility.button>

            <x-pos.utility.button
                type="submit"
                variant="primary"
                size="sm"
                icon="ph-check"
            >
                Simpan Presensi
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
