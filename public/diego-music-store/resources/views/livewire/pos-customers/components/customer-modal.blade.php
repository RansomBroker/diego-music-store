{{-- ===================== REUSABLE MODAL: FORM TAMBAH / EDIT ===================== --}}
<x-pos.modal
    wire:model="showModal"
    :title="$isEditing ? 'Ubah Pelanggan' : 'Tambah Pelanggan'"
    :subtitle="$isEditing ? 'Perbarui detail data pelanggan terpilih' : 'Tambahkan data pelanggan baru ke sistem'"
    :icon="$isEditing ? 'ph-pencil-simple' : 'ph-user-plus'"
    maxWidth="2xl"
>
    <form wire:submit.prevent="save" class="space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            {{-- Kolom Kiri: Informasi Kontak --}}
            <div class="space-y-4">
                <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Informasi Kontak</h4>

                <!-- Nama -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nama Pelanggan <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        wire:model="name"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                        placeholder="e.g. John Doe"
                    >
                    @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Telepon -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Nomor Telepon</label>
                    <input
                        type="tel"
                        wire:model="phone"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                        placeholder="e.g. 08123456789"
                    >
                    @error('phone') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Email</label>
                    <input
                        type="email"
                        wire:model="email"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                        placeholder="e.g. john@example.com"
                    >
                    @error('email') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Tanggal Lahir -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal Lahir</label>
                    <input
                        type="date"
                        wire:model="date_of_birth"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                    @error('date_of_birth') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Label -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Label Pelanggan</label>
                    <select
                        wire:model="customer_label_id"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                        <option value="">— Tanpa Label —</option>
                        @foreach ($labels as $label)
                            <option value="{{ $label->id }}">{{ $label->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Alamat -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Alamat</label>
                    <textarea
                        wire:model="address"
                        rows="2"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors resize-none"
                        placeholder="Alamat pelanggan..."
                    ></textarea>
                </div>
            </div>

            {{-- Kolom Kanan: Program Loyalty --}}
            <div class="space-y-4">
                <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider">Program Loyalty</h4>

                <!-- Toggle Loyalty Member -->
                <div class="flex items-center justify-between p-3.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl transition-colors">
                    <div>
                        <div class="text-sm font-semibold text-slate-700 dark:text-slate-200">Loyalty Member</div>
                        <div class="text-[11px] text-slate-400 dark:text-slate-555 mt-0.5">Berikan keuntungan akumulasi poin</div>
                    </div>
                    <button
                        type="button"
                        wire:click="$toggle('is_loyalty_member')"
                        class="relative w-10 h-5.5 rounded-full transition-colors duration-200 focus:outline-none cursor-pointer {{ $is_loyalty_member ? 'bg-primary' : 'bg-slate-300 dark:bg-slate-700' }}"
                    >
                        <span class="absolute top-0.5 left-0.5 w-4.5 h-4.5 rounded-full bg-white shadow-sm transition-transform duration-200 {{ $is_loyalty_member ? 'translate-x-4.5' : 'translate-x-0' }}"></span>
                    </button>
                </div>

                <!-- Poin Belanja -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Poin Belanja</label>
                    <div class="relative">
                        <i class="ph ph-star absolute left-3 top-1/2 -translate-y-1/2 text-amber-400 text-base"></i>
                        <input
                            type="number"
                            wire:model="loyalty_points"
                            min="0"
                            class="w-full pl-9 pr-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                            placeholder="0"
                        >
                    </div>
                    @error('loyalty_points') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Tingkat Harga Default -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tingkat Harga Default</label>
                    <select
                        wire:model="pricing_tier_id"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                    >
                        <option value="">— Harga Standar —</option>
                        @foreach ($pricingTiers as $tier)
                            <option value="{{ $tier->id }}">{{ $tier->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Info Box -->
                <div class="p-3.5 bg-blue-50/50 dark:bg-blue-950/20 border border-blue-100 dark:border-blue-900/30 rounded-xl transition-colors">
                    <div class="flex items-start gap-2.5">
                        <i class="ph-fill ph-info text-blue-500 dark:text-blue-400 text-base mt-0.5 flex-shrink-0"></i>
                        <p class="text-[11px] text-blue-700 dark:text-blue-400 leading-normal">
                            Pelanggan yang didaftarkan sebagai member loyalitas akan secara otomatis mengumpulkan poin dari setiap transaksi retail.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Buttons -->
        <div class="flex items-center justify-end gap-3 pt-5 border-t border-slate-200 dark:border-slate-800">
            <button
                type="button"
                wire:click="$set('showModal', false)"
                class="px-4 py-2 border border-slate-300 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
            >
                Batal
            </button>
            <button
                type="submit"
                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150 cursor-pointer"
            >
                <i class="ph-bold ph-check text-xs"></i>
                <span>{{ $isEditing ? 'Simpan Perubahan' : 'Tambah Pelanggan' }}</span>
            </button>
        </div>
    </form>
</x-pos.modal>
