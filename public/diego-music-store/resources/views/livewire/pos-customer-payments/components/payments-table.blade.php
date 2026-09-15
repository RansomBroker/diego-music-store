<!-- Table -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <x-pos.table.th sortable field="invoice_number" :sortField="$sortField" :sortDirection="$sortDirection">
                    No. Transaksi / Invoice
                </x-pos.table.th>
                <x-pos.table.th sortable field="invoice_date" :sortField="$sortField" :sortDirection="$sortDirection">
                    Tanggal
                </x-pos.table.th>
                <x-pos.table.th>
                    Pelanggan
                </x-pos.table.th>
                <x-pos.table.th>
                    Metode Bayar
                </x-pos.table.th>
                <x-pos.table.th class="text-right" sortable field="grand_total" :sortField="$sortField" :sortDirection="$sortDirection">
                    Total Tagihan / Piutang
                </x-pos.table.th>
                <x-pos.table.th class="text-center">
                    Status
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($payments as $payment)
                <x-pos.table.tr wire:click="showDetails({{ $payment->id }})" class="cursor-pointer hover:bg-slate-50/80 dark:hover:bg-slate-800/40 transition-colors">
                    <x-pos.table.td class="whitespace-nowrap font-mono font-medium text-slate-900 dark:text-slate-100">
                        {{ $payment->invoice_number }}
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-sm text-slate-600 dark:text-slate-355">
                        {{ $payment->invoice_date?->format('d/m/Y') }}
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap font-semibold text-slate-900 dark:text-slate-100">
                        {{ $payment->customer?->name ?? 'Pelanggan Umum' }}
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-sm">
                        {{ $payment->payment_method }}
                    </x-pos.table.td>
                    @php
                        $piutangDue = $payment->getPiutangAmount();
                    @endphp
                    <x-pos.table.td class="whitespace-nowrap text-right font-mono">
                        <div class="font-bold text-slate-900 dark:text-slate-100">Rp {{ number_format($payment->grand_total, 0, ',', '.') }}</div>
                        @if ($piutangDue > 0)
                            <div class="text-[11px] font-bold text-rose-500">Sisa: Rp {{ number_format($piutangDue, 0, ',', '.') }}</div>
                        @endif
                    </x-pos.table.td>
                    <x-pos.table.td class="whitespace-nowrap text-center">
                        @if ($piutangDue <= 0)
                            <span class="px-2.5 py-0.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-800 dark:text-emerald-350 text-xs font-bold rounded-full border border-emerald-200/50 dark:border-emerald-850/30">
                                Lunas (Completed)
                            </span>
                        @else
                            <span class="px-2.5 py-0.5 bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-350 text-xs font-bold rounded-full border border-amber-200/50 dark:border-amber-850/30">
                                Belum Lunas (Piutang)
                            </span>
                        @endif
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="6" icon="ph-hand-coins" message="Belum ada riwayat transaksi piutang pelanggan." />
            @endforelse
        </tbody>
    </x-pos.table>
    <x-pos.table.footer :paginator="$payments" />
</x-pos.table.container>
