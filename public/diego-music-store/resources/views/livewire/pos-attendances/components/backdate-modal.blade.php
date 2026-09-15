<!-- MODAL POPUP: REQUEST PRESENSI SUSULAN (BACKDATE) -->
<x-pos.modal wire:model="showBackdateModal" title="Form Request Presensi Susulan (Backdate)" maxWidth="lg">
    <form wire:submit.prevent="submitBackdateRequest" class="space-y-4">
        <div class="p-3 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 rounded-xl text-xs text-purple-800 dark:text-purple-300">
            💡 <strong>Informasi:</strong> Pengajuan ini akan dikirim ke Owner/Admin untuk diverifikasi. Setelah disetujui, presensi tanggal yang Anda ajukan akan otomatis tercatat sebagai "Hadir".
        </div>

        <!-- Tanggal Susulan -->
        <x-pos.form.input
            label="Tanggal Presensi Susulan"
            type="date"
            model="backdateDate"
            max="{{ now()->format('Y-m-d') }}"
            icon="ph-calendar"
            required
        />

        <!-- Grid Jam Masuk & Pulang -->
        <div class="grid grid-cols-2 gap-3">
            <x-pos.form.input
                label="Jam Masuk (Clock In)"
                type="time"
                model="backdateClockIn"
                icon="ph-clock"
                required
            />
            <x-pos.form.input
                label="Jam Pulang (Clock Out)"
                type="time"
                model="backdateClockOut"
                icon="ph-clock"
            />
        </div>

        <!-- Alasan Pengajuan -->
        <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1.5">
                Alasan / Keterangan Kendala <span class="text-rose-500">*</span>
            </label>
            <textarea
                wire:model="backdateReason"
                rows="3"
                required
                placeholder="Jelaskan alasan mengapa lupa absen atau kendala saat bertugas..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl outline-hidden font-bold text-sm focus:ring-2 focus:ring-primary dark:focus:ring-blue-500 text-slate-800 dark:text-slate-100"
            ></textarea>
            @error('backdateReason') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Upload Foto Bukti (Opsional) -->
        <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1.5">
                Upload Foto Bukti (Opsional)
            </label>
            <input
                type="file"
                wire:model="backdatePhoto"
                accept="image/*"
                class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 cursor-pointer"
            >
            @error('backdatePhoto') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Modal Footer -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                variant="secondary"
                size="sm"
                wire:click="$set('showBackdateModal', false)"
            >
                Batal
            </x-pos.utility.button>

            <x-pos.utility.button
                type="submit"
                variant="primary"
                size="sm"
                icon="ph-paper-plane-tilt"
            >
                Kirim Pengajuan
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
