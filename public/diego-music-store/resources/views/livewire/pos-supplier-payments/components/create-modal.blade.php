{{-- ===================== MODAL: PELUNASAN PIUTANG (CREATE) ===================== --}}
<x-pos.modal
    wire:model="showCreateModal"
    title="Input Pelunasan Piutang"
    subtitle="Proses pelunasan piutang transaksi penjualan pelanggan"
    icon="ph-plus"
    maxWidth="5xl"
>
    <div class="space-y-6">
        <!-- Informasi Utama -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <!-- Tanggal Pembayaran -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Tanggal Pelunasan <span class="text-rose-500">*</span></label>
                <input
                    type="date"
                    wire:model="payment_date"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                >
                @error('payment_date') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Pelanggan -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Pelanggan <span class="text-rose-500">*</span></label>
                <select
                    wire:model.live="customer_id"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                >
                    <option value="">Pilih Pelanggan</option>
                    @foreach ($customers as $customer)
                        <option value="{{ $customer->id }}">{{ $customer->name }} (Hutang: Rp {{ number_format($customer->outstanding_debt, 0, ',', '.') }})</option>
                    @endforeach
                </select>
                @error('customer_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Akun Kas / Bank -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Akun Kas / Bank <span class="text-rose-500">*</span></label>
                <select
                    wire:model="account_id"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                >
                    <option value="">Pilih Akun Kas/Bank</option>
                    @foreach ($accounts as $acc)
                        <option value="{{ $acc->id }}">{{ $acc->code }} - {{ $acc->name }}</option>
                    @endforeach
                </select>
                @error('account_id') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Metode Pembayaran -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Metode Pelunasan <span class="text-rose-500">*</span></label>
                <select
                    wire:model="payment_method"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                >
                    <option value="Tunai">Tunai / Cash</option>
                    <option value="Transfer BCA">Transfer BCA</option>
                    <option value="Transfer Mandiri">Transfer Mandiri</option>
                    <option value="Transfer BNI">Transfer BNI</option>
                    <option value="QRIS">QRIS</option>
                    <option value="Debit Card">Debit Card</option>
                </select>
                @error('payment_method') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Referensi Pembayaran -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Referensi Pembayaran</label>
                <input
                    type="text"
                    wire:model="payment_reference"
                    placeholder="e.g. No. Rek / Bukti Transfer"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors"
                >
                @error('payment_reference') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Catatan -->
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1.5">Catatan</label>
                <textarea
                    wire:model="notes"
                    rows="1"
                    placeholder="Tambahkan catatan khusus..."
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors resize-none"
                ></textarea>
                @error('notes') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- Invoices List -->
        <div>
            <h4 class="text-xs font-bold text-slate-450 dark:text-slate-500 uppercase tracking-wider mb-3">Rincian Invoice Pembelian yang Dibayar</h4>

            @if (empty($items))
                <div class="p-6 border border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-center text-slate-400 dark:text-slate-500">
                    <i class="ph ph-receipt text-3xl mb-1.5"></i>
                    <p class="text-sm font-medium">Pilih supplier terlebih dahulu untuk menampilkan daftar invoice yang belum lunas.</p>
                </div>
            @else
                <x-pos.table.container>
                    <x-pos.table>
                        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
                            <tr>
                                <th class="px-5 py-3 text-left w-12">Pilih</th>
                                <x-pos.table.th>No. Invoice</x-pos.table.th>
                                <x-pos.table.th>Tanggal</x-pos.table.th>
                                <x-pos.table.th class="text-right">Total</x-pos.table.th>
                                <x-pos.table.th class="text-right">Sisa Hutang</x-pos.table.th>
                                <x-pos.table.th class="text-right w-44">Jumlah Bayar</x-pos.table.th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                            @foreach ($items as $idx => $item)
                                <x-pos.table.tr>
                                    <td class="px-5 py-3">
                                        <input
                                            type="checkbox"
                                            wire:model.live="items.{{ $idx }}.is_selected"
                                            wire:change="toggleItemSelection({{ $idx }})"
                                            class="rounded border-slate-300 dark:border-slate-700 text-primary focus:ring-primary w-4.5 h-4.5 transition cursor-pointer"
                                        >
                                    </td>
                                    <x-pos.table.td class="whitespace-nowrap font-medium text-slate-900 dark:text-slate-100">
                                        <div class="font-mono font-bold">{{ $item['invoice_number'] }}</div>
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">
                                        {{ date('d/m/Y', strtotime($item['transaction_date'])) }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-right text-sm text-slate-600 dark:text-slate-355 font-mono">
                                        Rp {{ number_format($item['grand_total'], 0, ',', '.') }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-right font-semibold text-slate-900 dark:text-slate-200 font-mono">
                                        Rp {{ number_format($item['amount_due'], 0, ',', '.') }}
                                    </x-pos.table.td>
                                    <x-pos.table.td class="whitespace-nowrap text-right">
                                        <x-money-input
                                            wire:model.live.debounce.300ms="items.{{ $idx }}.amount_paid"
                                            class="w-full px-3 py-1.5 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-right text-slate-900 dark:text-white font-bold focus:border-primary dark:focus:border-blue-500 focus:ring-1 focus:ring-primary dark:focus:ring-blue-500 focus:outline-none transition-colors font-mono"
                                            placeholder="0"
                                        />
                                    </x-pos.table.td>
                                </x-pos.table.tr>
                            @endforeach
                        </tbody>
                    </x-pos.table>
                </x-pos.table.container>

                @php
                    $totalOutstanding = collect($items)->sum('amount_due');
                    $totalPayment = collect($items)->filter(fn($item) => $item['is_selected'] ?? false)->sum('amount_paid');
                @endphp

                <!-- Summary Area -->
                <div class="mt-4 p-5 bg-slate-50 dark:bg-slate-900 rounded-2xl border border-slate-200/60 dark:border-slate-800/80 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 transition-colors">
                    <div>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Total Outstanding</span>
                        <span class="text-lg font-black text-slate-850 dark:text-slate-100 font-mono">Rp {{ number_format($totalOutstanding, 0, ',', '.') }}</span>
                    </div>
                    <div class="text-right">
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider block mb-1">Total Pembayaran</span>
                        <span class="text-2xl font-black text-primary dark:text-blue-400 font-mono">Rp {{ number_format($totalPayment, 0, ',', '.') }}</span>
                    </div>
                </div>
            @endif
        </div>

        <!-- Footer Buttons -->
        <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100 dark:border-slate-700 flex-shrink-0">
            <button
                type="button"
                wire:click="$set('showCreateModal', false)"
                class="px-5 py-2 border border-slate-350 hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition-colors cursor-pointer"
            >
                Batal
            </button>
            <button
                type="button"
                wire:click="save('draft')"
                wire:loading.attr="disabled"
                class="px-5 py-2 border border-primary text-primary hover:bg-primary-light dark:border-blue-500 dark:text-blue-400 dark:hover:bg-blue-950/20 text-sm font-bold rounded-xl transition-colors cursor-pointer disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="save('draft')">Simpan Draft</span>
                <span wire:loading wire:target="save('draft')">Menyimpan...</span>
            </button>
            <button
                type="button"
                wire:click="save('posted')"
                wire:loading.attr="disabled"
                class="px-5 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-xl shadow-md hover:shadow transition duration-150 cursor-pointer active:scale-[0.98] disabled:opacity-50"
            >
                <span wire:loading.remove wire:target="save('posted')">Simpan & Posting</span>
                <span wire:loading wire:target="save('posted')">Memproses...</span>
            </button>
        </div>
    </div>
</x-pos.modal>
