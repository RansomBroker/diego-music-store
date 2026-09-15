<!-- UPDATE SERVICE ORDER MODAL -->
@if ($showEditModal)
    <div class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/60 backdrop-blur-xs flex items-center justify-center p-4">
        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-150">
            
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex items-center justify-between bg-slate-50 dark:bg-slate-800/50">
                <div>
                    <h3 class="font-bold text-slate-900 dark:text-white text-base">
                        Update Tiket Service
                    </h3>
                    <p class="text-xs text-slate-500 font-mono">Kode Tiket: {{ \App\Models\ServiceOrder::find($editingOrderId)?->ticket_code }}</p>
                </div>
                <button wire:click="closeEditModal" type="button" class="p-1 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 rounded-lg cursor-pointer">
                    <i class="ph ph-x text-xl"></i>
                </button>
            </div>

            <form wire:submit.prevent="saveServiceOrder" class="p-6 space-y-4 text-xs">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Status Progress Service</label>
                        <x-pos.form.select model="editStatus">
                            <option value="received">Diterima</option>
                            <option value="diagnosing">Proses Diagnosa</option>
                            <option value="in_progress">Dikerjakan</option>
                            <option value="waiting_parts">Menunggu Sparepart</option>
                            <option value="completed">Selesai Service</option>
                            <option value="picked_up">Siap / Sudah Diambil</option>
                            <option value="cancelled">Dibatalkan</option>
                        </x-pos.form.select>
                    </div>

                    <div>
                        <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Teknisi Penanggung Jawab</label>
                        <x-pos.form.select model="editTechnicianId">
                            <option value="">Pilih Teknisi</option>
                            @foreach ($technicians as $tech)
                                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
                            @endforeach
                        </x-pos.form.select>
                    </div>
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Nomor Seri Unit (S/N)</label>
                    <x-pos.form.input model="editSerialNumber" placeholder="Contoh: S/N F310-998821" />
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Keluhan / Gejala Kerusakan</label>
                    <textarea wire:model="editComplaint" rows="2" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-2.5" placeholder="Deskripsi keluhan dari pelanggan..."></textarea>
                </div>

                <div>
                    <label class="font-bold text-slate-700 dark:text-slate-300 block mb-1">Catatan Pengerjaan Teknisi</label>
                    <textarea wire:model="editNotes" rows="2" class="w-full text-xs rounded-xl border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900 text-slate-900 dark:text-white p-2.5" placeholder="Catatan hasil diagnosa, tindakan perbaikan, dll..."></textarea>
                </div>

                <!-- Additional Spareparts / Labor Section -->
                <div class="border-t border-slate-200 dark:border-slate-800 pt-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <label class="font-bold text-slate-700 dark:text-slate-300">Sparepart & Layanan Tambahan</label>
                        <x-pos.utility.button type="button" variant="primary" size="xs" icon="ph-plus" wire:click="addAdditionalChargeRow">
                            Tambah Item
                        </x-pos.utility.button>
                    </div>

                    @foreach ($editAdditionalCharges as $idx => $chg)
                        <div class="flex items-center gap-2">
                            <div class="flex-1">
                                <x-pos.form.input model="editAdditionalCharges.{{ $idx }}.name" placeholder="Nama sparepart/jasa tambahan..." />
                            </div>
                            <div class="w-36">
                                <x-pos.form.input type="text" :currency="true" model="editAdditionalCharges.{{ $idx }}.amount" placeholder="Nominal (Rp)" />
                            </div>
                            <x-pos.utility.button type="button" variant="danger" size="xs" icon="ph-trash" wire:click="removeAdditionalChargeRow({{ $idx }})" title="Hapus Item" />
                        </div>
                    @endforeach
                </div>

                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end gap-2">
                    <x-pos.utility.button type="button" variant="secondary" size="sm" wire:click="closeEditModal">
                        Batal
                    </x-pos.utility.button>
                    <x-pos.utility.button type="submit" variant="primary" size="sm" icon="ph-check">
                        Simpan Perubahan
                    </x-pos.utility.button>
                </div>
            </form>
        </div>
    </div>
@endif
