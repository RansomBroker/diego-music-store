<!-- Modal Form (Tambah / Edit Karyawan) -->
<x-pos.modal
    wire:model="showModal"
    :title="$isEditing ? 'Edit Data Karyawan' : 'Tambah Karyawan Baru'"
    :subtitle="$isEditing ? 'Perbarui informasi data karyawan terpilih' : 'Isikan data personel karyawan baru ke sistem'"
    :icon="$isEditing ? 'ph-pencil-simple' : 'ph-user-plus'"
    maxWidth="2xl"
>
    <form wire:submit.prevent="save" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- NIK -->
            <x-pos.form.input
                label="NIK (Nomor Induk Karyawan)"
                model="nik"
                placeholder="Otomatis jika kosong..."
                icon="ph-identification-card"
            />

            <!-- Nama Lengkap -->
            <x-pos.form.input
                label="Nama Lengkap Karyawan"
                model="name"
                placeholder="Contoh: Budi Santoso"
                icon="ph-user"
                required
            />

            <!-- No Telepon -->
            <x-pos.form.input
                label="No Telepon / WhatsApp"
                model="phone"
                placeholder="08123456789"
                icon="ph-phone"
            />

            <!-- Email -->
            <x-pos.form.input
                label="Email"
                type="email"
                model="email"
                placeholder="karyawan@diegomusic.com"
                icon="ph-envelope"
            />

            <!-- Cabang -->
            <x-pos.form.dropdown
                label="Cabang Karyawan"
                model="branch_id"
                icon="ph-buildings"
                placeholder="-- Pilih Cabang --"
            >
                @foreach ($branches as $b)
                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                @endforeach
            </x-pos.form.dropdown>

            <!-- Akun User Login -->
            <x-pos.form.dropdown
                label="Hubungkan ke Akun Login User"
                model="user_id"
                icon="ph-user-circle"
                placeholder="-- Tanpa Akun Login --"
            >
                @foreach ($users as $u)
                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->username }})</option>
                @endforeach
            </x-pos.form.dropdown>

            <!-- Quota Off Day -->
            <x-pos.form.input
                label="Kuota Off Day per Bulan"
                type="number"
                min="0"
                model="monthly_off_days_quota"
                suffix="Hari"
                required
            />

            <!-- Gaji Pokok -->
            <x-pos.form.input
                label="Gaji Pokok"
                type="currency"
                model="basic_salary"
                placeholder="0"
                required
            />
        </div>

        <!-- Alamat -->
        <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1.5">
                Alamat Lengkap
            </label>
            <textarea
                wire:model="address"
                rows="2"
                placeholder="Jl. Merdeka No. 10..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl outline-hidden focus:outline-hidden font-bold text-sm focus:ring-2 focus:ring-primary dark:focus:ring-blue-500 text-slate-800 dark:text-slate-100"
            ></textarea>
            @error('address') <span class="text-xs text-rose-500 font-semibold mt-1 block">{{ $message }}</span> @enderror
        </div>

        <!-- Status Aktif -->
        <x-pos.form.toggle
            label="Status Karyawan"
            sublabel="Aktifkan akun status karyawan ini"
            model="is_active"
        />

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                type="button"
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
                Simpan
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
