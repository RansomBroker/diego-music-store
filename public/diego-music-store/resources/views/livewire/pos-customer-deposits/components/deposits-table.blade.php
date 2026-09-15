<!-- Table Scrollable Area -->
<div class="overflow-x-auto relative">
    <!-- Wire Loading Progress Bar -->
    <div wire:loading.block class="absolute top-0 left-0 right-0 h-0.5 bg-primary/20 overflow-hidden">
        <div class="h-full bg-primary animate-pulse w-1/3"></div>
    </div>

    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-800">
            <tr>
                <x-pos.table.th sortable field="deposit_number" :sortField="$sortField" :sortDirection="$sortDirection">
                    No. Deposit & Tgl
                </x-pos.table.th>
                <x-pos.table.th>
                    Pelanggan
                </x-pos.table.th>
                <x-pos.table.th>
                    Produk Pesanan
                </x-pos.table.th>
                <x-pos.table.th class="text-right" sortable field="total_amount" :sortField="$sortField" :sortDirection="$sortDirection">
                    Total Pesanan
                </x-pos.table.th>
                <x-pos.table.th class="text-right" sortable field="deposit_amount" :sortField="$sortField" :sortDirection="$sortDirection">
                    Deposit (Uang Muka)
                </x-pos.table.th>
                <x-pos.table.th class="text-right" sortable field="remaining_amount" :sortField="$sortField" :sortDirection="$sortDirection">
                    Sisa Pelunasan
                </x-pos.table.th>
                <x-pos.table.th class="text-center" sortable field="status" :sortField="$sortField" :sortDirection="$sortDirection">
                    Status
                </x-pos.table.th>
                <x-pos.table.th class="text-center">
                    Aksi
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
            @forelse ($deposits as $deposit)
                <x-pos.table.tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                    <!-- No. Deposit & Date -->
                    <x-pos.table.td class="font-semibold text-slate-900 dark:text-white">
                        <div class="flex flex-col">
                            <span class="font-bold text-primary dark:text-blue-400">{{ $deposit->deposit_number }}</span>
                            <span class="text-[11px] text-slate-400 font-normal">
                                {{ $deposit->deposit_date->format('d/m/Y') }}
                            </span>
                        </div>
                    </x-pos.table.td>

                    <!-- Customer -->
                    <x-pos.table.td>
                        <div class="flex flex-col">
                            <span class="font-bold text-slate-800 dark:text-slate-200">{{ $deposit->customer?->name ?? '-' }}</span>
                            <span class="text-[11px] text-slate-400">{{ $deposit->customer?->phone ?? '-' }}</span>
                        </div>
                    </x-pos.table.td>

                    <!-- Product -->
                    <x-pos.table.td>
                        <div class="flex flex-col">
                            <div class="flex items-center gap-1.5">
                                @if ($deposit->product_type === 'manual')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-purple-100 text-purple-700 dark:bg-purple-950/60 dark:text-purple-300">
                                        PO MANUAL
                                    </span>
                                @else
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-black bg-blue-100 text-blue-700 dark:bg-blue-950/60 dark:text-blue-300">
                                        KATALOG
                                    </span>
                                @endif
                                <span class="font-semibold text-slate-800 dark:text-slate-200 truncate max-w-[200px]" title="{{ $deposit->product_name }}">
                                    {{ $deposit->product_name }}
                                </span>
                            </div>
                            <span class="text-[11px] text-slate-400 mt-0.5">
                                {{ $deposit->qty }} unit &times; Rp {{ number_format($deposit->price, 0, ',', '.') }}
                            </span>
                        </div>
                    </x-pos.table.td>

                    <!-- Total Amount -->
                    <x-pos.table.td class="text-right font-bold text-slate-900 dark:text-white font-mono">
                        Rp {{ number_format($deposit->total_amount, 0, ',', '.') }}
                    </x-pos.table.td>

                    <!-- Deposit Paid -->
                    <x-pos.table.td class="text-right font-mono">
                        <span class="font-bold text-emerald-600 dark:text-emerald-400">
                            Rp {{ number_format($deposit->deposit_amount, 0, ',', '.') }}
                        </span>
                        <div class="text-[10px] text-slate-400 font-sans">{{ $deposit->payment_method }}</div>
                    </x-pos.table.td>

                    <!-- Remaining Amount -->
                    <x-pos.table.td class="text-right font-mono">
                        @if ($deposit->remaining_amount > 0 && $deposit->isPending())
                            <span class="font-extrabold text-amber-600 dark:text-amber-400">
                                Rp {{ number_format($deposit->remaining_amount, 0, ',', '.') }}
                            </span>
                        @else
                            <span class="text-slate-400 dark:text-slate-500 font-semibold">
                                Rp 0
                            </span>
                        @endif
                    </x-pos.table.td>

                    <!-- Status Badge -->
                    <x-pos.table.td class="text-center">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $deposit->getStatusBadgeClass() }}">
                            {{ $deposit->getStatusLabel() }}
                        </span>
                    </x-pos.table.td>

                    <!-- Actions -->
                    <x-pos.table.td class="text-center">
                        <div class="inline-flex items-center gap-1.5 justify-center">
                            @if ($deposit->isPending())
                                <!-- Pelunasan Button -->
                                <button
                                    type="button"
                                    wire:click="openSettleModal({{ $deposit->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs transition active:scale-95 cursor-pointer"
                                    title="Pelunasan Pesanan"
                                >
                                    <i class="ph-bold ph-check-circle text-sm"></i>
                                    <span>Pelunasan</span>
                                </button>

                                <!-- Edit Button -->
                                <button
                                    type="button"
                                    wire:click="openEditModal({{ $deposit->id }})"
                                    class="p-1.5 rounded-lg bg-amber-500 hover:bg-amber-600 text-white shadow-xs transition active:scale-95 cursor-pointer"
                                    title="Edit Deposit"
                                >
                                    <i class="ph-bold ph-pencil-simple text-sm"></i>
                                </button>

                                <!-- Delete / Cancel Button -->
                                <button
                                    type="button"
                                    wire:confirm="Yakin ingin membatalkan deposit ini? Jurnal setoran akan dibatalkan."
                                    wire:click="cancelDeposit({{ $deposit->id }})"
                                    class="p-1.5 rounded-lg bg-rose-500 hover:bg-rose-600 text-white shadow-xs transition active:scale-95 cursor-pointer"
                                    title="Batalkan Deposit"
                                >
                                    <i class="ph-bold ph-trash text-sm"></i>
                                </button>
                            @else
                                <span class="text-xs text-slate-400 italic">Selesai</span>
                            @endif
                        </div>
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="8" icon="ph-hand-coins" message="Belum ada data deposit pelanggan. Klik Tambah Deposit Baru untuk memulai." />
            @endforelse
        </tbody>
    </x-pos.table>
</div>

<!-- Table Footer Component -->
<x-pos.table.footer :paginator="$deposits" perPageModel="perPage" />
