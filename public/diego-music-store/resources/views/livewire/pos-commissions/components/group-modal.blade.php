{{-- ===================== MODAL FORM: GRUP KOMISI ===================== --}}
<x-pos.modal
    wire:model="showGroupModal"
    :title="$editingGroupId ? 'Ubah Grup Komisi' : 'Tambah Grup Komisi Baru'"
    subtitle="Kelola grup tim penjualan, leader penerima, dan target bulanan anggota"
    icon="ph-users-four"
    maxWidth="2xl"
>
    <form wire:submit.prevent="saveGroup" class="space-y-5">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Nama Grup -->
            <div class="sm:col-span-2">
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Nama Grup Penjualan <span class="text-rose-500">*</span>
                </label>
                <input
                    type="text"
                    wire:model="groupName"
                    placeholder="Contoh: Tim Sales Retail Gitar"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"
                >
                @error('groupName') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Leader Penerima Komisi -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Leader Penerima Komisi Grup <span class="text-rose-500">*</span>
                </label>
                <select
                    wire:model="groupLeaderEmployeeId"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition"
                >
                    <option value="">-- Pilih Karyawan Leader --</option>
                    @foreach ($employees as $emp)
                        <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->nik }})</option>
                    @endforeach
                </select>
                @error('groupLeaderEmployeeId') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Rate Komisi (%) -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1.5">
                    Persentase Komisi (%) <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                    <input
                        type="number"
                        step="0.01"
                        min="0"
                        max="100"
                        wire:model="groupRate"
                        placeholder="0.10"
                        class="w-full pl-3 pr-8 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition font-mono font-semibold"
                    >
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs font-bold">%</span>
                </div>
                @error('groupRate') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Section: Anggota Grup & Target Penjualan -->
        <div class="pt-2 border-t border-slate-200 dark:border-slate-800 space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300 uppercase tracking-wider">
                        Anggota Tim & Target Penjualan Bulanan
                    </h4>
                    <p class="text-[11px] text-slate-400">
                        Setiap anggota wajib mencapai target ini agar komisi grup dapat terbuka.
                    </p>
                </div>
                <button
                    type="button"
                    wire:click="addGroupMemberRow"
                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-900/40 hover:bg-emerald-100 transition cursor-pointer"
                >
                    <i class="ph-bold ph-plus text-xs"></i>
                    Tambah Anggota
                </button>
            </div>

            @error('groupMembers') <span class="text-xs text-rose-500 block">{{ $message }}</span> @enderror

            <div class="space-y-2.5 max-h-64 overflow-y-auto pr-1">
                @forelse ($groupMembers as $index => $item)
                    <div class="flex items-center gap-2 p-2.5 rounded-lg bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800">
                        <!-- Pilih Karyawan -->
                        <div class="flex-1">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Anggota #{{ $index + 1 }}</label>
                            <select
                                wire:model="groupMembers.{{ $index }}.employee_id"
                                class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white outline-none focus:border-primary"
                            >
                                <option value="">-- Pilih Anggota --</option>
                                @foreach ($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->nik }})</option>
                                @endforeach
                            </select>
                            @error("groupMembers.{$index}.employee_id") <span class="text-[10px] text-rose-500 block mt-0.5">{{ $message }}</span> @enderror
                        </div>

                        <!-- Target Penjualan Bulanan -->
                        <div class="w-44">
                            <label class="block text-[10px] font-semibold text-slate-500 mb-1">Target Bulanan (Rp)</label>
                            <input
                                type="number"
                                step="1000"
                                min="0"
                                wire:model="groupMembers.{{ $index }}.monthly_target_amount"
                                placeholder="0"
                                class="w-full px-2.5 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-mono text-slate-900 dark:text-white outline-none focus:border-primary text-right"
                            >
                            @error("groupMembers.{$index}.monthly_target_amount") <span class="text-[10px] text-rose-500 block mt-0.5">{{ $message }}</span> @enderror
                        </div>

                        <!-- Tombol Hapus Baris -->
                        <div class="pt-4">
                            <button
                                type="button"
                                wire:click="removeGroupMemberRow({{ $index }})"
                                class="p-1.5 rounded-lg text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-950/20 transition cursor-pointer"
                                title="Hapus Anggota Ini"
                            >
                                <i class="ph-bold ph-trash text-sm"></i>
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-4 text-xs text-slate-400 border border-dashed border-slate-200 dark:border-slate-800 rounded-lg">
                        Klik tombol <strong>+ Tambah Anggota</strong> untuk memasukkan anggota tim ke grup ini.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                type="button"
                variant="secondary"
                wire:click="$set('showGroupModal', false)"
            >
                Batal
            </x-pos.utility.button>

            <x-pos.utility.button
                type="submit"
                variant="primary"
                icon="ph-check"
            >
                {{ $editingGroupId ? 'Simpan Perubahan' : 'Buat Grup Komisi' }}
            </x-pos.utility.button>
        </div>
    </form>
</x-pos.modal>
