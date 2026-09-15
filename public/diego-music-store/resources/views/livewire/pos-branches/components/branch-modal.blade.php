<!-- CREATE / EDIT BRANCH MODAL -->
@if ($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-3xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">
                        {{ $isEditing ? 'Edit Profil Cabang' : 'Tambah Cabang Baru' }}
                    </h3>
                    <p class="text-xs text-slate-500">
                        {{ $isEditing ? 'Perbarui informasi outlet cabang.' : 'Cabang baru akan otomatis dikonfigurasi dengan stok awal & receipt setting.' }}
                    </p>
                </div>
                <button wire:click="$set('showModal', false)" type="button" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <form wire:submit.prevent="save" class="p-6 space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Nama Cabang (Internal) *</label>
                        <x-pos.form.input model="name" placeholder="Contoh: Cabang Siantan / Cabang Gajah Mada" />
                        @error('name') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Nama Toko (Untuk Struk & Faktur) *</label>
                        <x-pos.form.input model="store_name" placeholder="Contoh: Diego Music Store Siantan" />
                        @error('store_name') <span class="text-rose-500 text-[10px]">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Telepon</label>
                        <x-pos.form.input model="phone" placeholder="0561-xxxxxx" />
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Email Cabang</label>
                        <x-pos.form.input model="email" placeholder="cabang@diegomusic.com" />
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Manager Cabang</label>
                        <x-pos.form.select model="manager_id">
                            <option value="">Pilih Manager</option>
                            @foreach ($managers as $m)
                                <option value="{{ $m->id }}">{{ $m->name }}</option>
                            @endforeach
                        </x-pos.form.select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Kota / Kabupaten</label>
                        <x-pos.form.input model="city" placeholder="Pontianak" />
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Provinsi</label>
                        <x-pos.form.input model="province" placeholder="Kalimantan Barat" />
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Kode Pos</label>
                        <x-pos.form.input model="postal_code" placeholder="78123" />
                    </div>
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Alamat Lengkap</label>
                    <textarea wire:model="address" rows="2" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-2.5" placeholder="Jl. Gajah Mada No. 21-22..."></textarea>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">NPWP Toko</label>
                        <x-pos.form.input model="npwp" placeholder="00.000.000.0-000.000" />
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Informasi Bank / Rekening</label>
                        <x-pos.form.input model="bank_info" placeholder="BCA 123456789 a.n Diego Music" />
                    </div>
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Upload Logo Cabang (Opsional)</label>
                    <input type="file" wire:model="logo" class="w-full text-xs border border-slate-200 dark:border-slate-800 rounded-xl p-2 bg-white dark:bg-slate-900">
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" wire:model="is_active" id="is_active_cb" class="w-4 h-4 rounded text-blue-600">
                    <label for="is_active_cb" class="font-bold text-slate-700 dark:text-slate-300">Status Cabang Aktif</label>
                </div>

                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                    <x-pos.utility.button type="button" variant="secondary" size="sm" wire:click="$set('showModal', false)">
                        Batal
                    </x-pos.utility.button>
                    <x-pos.utility.button type="submit" variant="primary" size="sm" icon="ph-check">
                        {{ $isEditing ? 'Simpan Perubahan' : 'Buat Cabang Baru' }}
                    </x-pos.utility.button>
                </div>
            </form>
        </div>
    </div>
@endif
