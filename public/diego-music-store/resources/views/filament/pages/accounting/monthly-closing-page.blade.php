<x-filament-panels::page>
    @php
        $info = $this->period_info;
        $history = $this->history;
        $reopenTarget = $this->selectedClosingIdToReopen ? \App\Models\MonthlyClosing::find($this->selectedClosingIdToReopen) : $info['closing'];
    @endphp

    {{-- Filter Form (Period Picker) --}}
    <div>
        {{ $this->form }}
    </div>

    {{-- Status Banner & Summary Preview Card --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full {{ $info['is_closed'] ? 'bg-gray-800 dark:bg-white' : 'bg-blue-600 dark:bg-blue-400' }}"></span>
                <span class="font-extrabold tracking-wide text-gray-900 dark:text-white">STATUS & RINGKASAN KEUANGAN PERIODE</span>
            </div>
        </x-slot>

        <x-slot name="headerEnd">
            <span class="px-3 py-1 text-xs font-bold uppercase rounded-full border {{ $info['is_closed'] ? 'bg-gray-900 text-white dark:bg-gray-800 dark:text-white dark:border-gray-600 border-gray-900' : 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100 border-blue-300 dark:border-blue-500 font-extrabold' }}">
                {{ $info['is_closed'] ? 'STATUS: DITUTUP (CLOSED)' : 'STATUS: TERBUKA (OPEN)' }}
            </span>
        </x-slot>

        <div class="space-y-6 -mx-6 -mb-6 p-6 bg-white dark:bg-gray-900 border-t border-gray-200 dark:border-white/10">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                <div class="p-4 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-800 rounded-xl">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total Pendapatan (Revenue)</span>
                    <div class="text-lg font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                        {{ \App\Helpers\FinancialReportHelper::formatRupiah($info['total_revenue']) }}
                    </div>
                </div>

                <div class="p-4 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-800 rounded-xl">
                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider block">Total HPP & Beban Operasional</span>
                    <div class="text-lg font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                        {{ \App\Helpers\FinancialReportHelper::formatRupiah($info['total_cogs'] + $info['total_expense']) }}
                    </div>
                </div>

                <div class="p-4 bg-gray-100 dark:bg-white/10 border-2 border-gray-400 dark:border-gray-600 rounded-xl">
                    <span class="text-xs font-extrabold text-gray-900 dark:text-white uppercase tracking-wider block">
                        {{ $info['is_profit'] ? 'LABA BERSIH (NET PROFIT)' : 'RUGI BERSIH (NET LOSS)' }}
                    </span>
                    <div class="text-xl font-extrabold font-mono text-gray-900 dark:text-white mt-1">
                        {{ \App\Helpers\FinancialReportHelper::formatRupiah($info['net_income']) }}
                    </div>
                </div>
            </div>

            {{-- Action Controls & Notification --}}
            <div class="p-4 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-800 rounded-xl flex flex-col md:flex-row justify-between items-center gap-4">
                @if(!$info['is_closed'])
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        <strong class="text-gray-900 dark:text-white block text-sm">Siap Mengeksekusi Tutup Buku Bulanan?</strong>
                        Sistem akan mengunci transaksi pada periode ini dan otomatis membuat <strong>Jurnal Penutup #JV-CLOSE-{{ str_replace('-', '', $info['period_key']) }}</strong> yang mentransfer Laba/Rugi Bersih sebesar <strong>{{ \App\Helpers\FinancialReportHelper::formatRupiah($info['net_income']) }}</strong> ke Akun Laba Ditahan (3-2000).
                    </div>

                    <button 
                        type="button" 
                        wire:click="openClosingModal"
                        class="px-5 py-2.5 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 rounded-lg shadow-md transition-all whitespace-nowrap"
                    >
                        Proses Tutup Buku Bulanan
                    </button>
                @else
                    <div class="text-xs text-gray-600 dark:text-gray-400">
                        <strong class="text-gray-900 dark:text-white block text-sm">Periode Keuangan Ini Telah Ditutup (Closed)</strong>
                        Ditutup pada: <strong>{{ \Illuminate\Support\Carbon::parse($info['closing']->closed_at)->translatedFormat('d F Y H:i') }}</strong> oleh <strong>{{ $info['closing']->closedBy?->name ?? 'Admin' }}</strong>. Jurnal Penutup: <strong>{{ $info['closing']->closingJournal?->entry_no ?? '-' }}</strong>.
                    </div>

                    <span class="px-3.5 py-1.5 text-xs font-bold uppercase rounded-lg border bg-gray-300 text-gray-900 dark:bg-gray-800 dark:text-gray-200 border-gray-400 dark:border-gray-600">
                        Periode Dikunci
                    </span>
                @endif
            </div>
        </div>
    </x-filament::section>

    {{-- History Table Section --}}
    <x-filament::section>
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-gray-700 dark:bg-gray-300"></span>
                <span class="font-extrabold tracking-wide">RIWAYAT PENUTUPAN BUKU BULANAN</span>
            </div>
        </x-slot>

        <div class="divide-y divide-gray-200 dark:divide-gray-800 -mx-6 -mb-6 border-t border-gray-200 dark:border-white/10 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="bg-gray-100/70 dark:bg-white/5 text-gray-700 dark:text-gray-300 text-xs uppercase font-bold border-b border-gray-300 dark:border-gray-700">
                        <th class="py-2.5 px-6">Periode</th>
                        <th class="py-2.5 px-6">Cabang</th>
                        <th class="py-2.5 px-6">Status</th>
                        <th class="py-2.5 px-6 text-right">Pendapatan</th>
                        <th class="py-2.5 px-6 text-right">Beban/HPP</th>
                        <th class="py-2.5 px-6 text-right">Laba/Rugi Bersih</th>
                        <th class="py-2.5 px-6">Jurnal Penutup</th>
                        <th class="py-2.5 px-6">Waktu Eksekusi</th>
                        <th class="py-2.5 px-6 text-center w-28">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @forelse($history as $item)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-white/5 text-gray-700 dark:text-gray-300">
                            <td class="py-2.5 px-6 font-mono text-xs font-bold text-gray-900 dark:text-white">{{ $item['period_key'] }}</td>
                            <td class="py-2.5 px-6 text-xs">{{ $item->branch?->name ?? 'Konsolidasi' }}</td>
                            <td class="py-2.5 px-6">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded border {{ $item['status'] === 'closed' ? 'bg-gray-900 text-white dark:bg-gray-800 dark:text-white dark:border-gray-600 border-gray-900' : 'bg-blue-100 text-blue-900 dark:bg-blue-900 dark:text-blue-100 border-blue-300 dark:border-blue-500 font-extrabold' }}">
                                    {{ strtoupper($item['status']) }}
                                </span>
                            </td>
                            <td class="py-2.5 px-6 text-right font-mono text-xs">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['total_revenue']) }}</td>
                            <td class="py-2.5 px-6 text-right font-mono text-xs">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['total_expense']) }}</td>
                            <td class="py-2.5 px-6 text-right font-mono text-xs font-bold text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($item['net_income']) }}</td>
                            <td class="py-2.5 px-6 font-mono text-xs font-semibold">{{ $item->closingJournal?->entry_no ?? '-' }}</td>
                            <td class="py-2.5 px-6 text-xs text-gray-500">
                                {{ \Illuminate\Support\Carbon::parse($item['closed_at'])->format('d/m/Y H:i') }}
                                <div class="text-[10px] text-gray-400">Oleh: {{ $item->closedBy?->name ?? 'Admin' }}</div>
                            </td>
                            <td class="py-2.5 px-6 text-center">
                                @if($item['status'] === 'closed')
                                    <button 
                                        type="button" 
                                        wire:click="openReopenModal({{ $item['id'] }})"
                                        class="px-3 py-1 text-xs font-bold text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-500 rounded-md shadow-sm transition-all whitespace-nowrap"
                                        title="Buka Kembali Periode Keuangan Ini"
                                    >
                                        Buka Kembali
                                    </button>
                                @else
                                    <span class="text-xs font-extrabold text-blue-600 dark:text-blue-400">TERBUKA</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="py-6 text-center text-gray-400 text-xs">
                                Belum ada riwayat penutupan buku bulanan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

    {{-- INTERACTIVE POPUP MODAL DIALOG: KONFIRMASI TUTUP BUKU BULANAN --}}
    @if($this->showClosingModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
            <div class="w-full max-w-md bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                
                {{-- Modal Header --}}
                <div class="p-5 bg-gray-50/80 dark:bg-white/5 border-b border-gray-200 dark:border-white/10 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                            Konfirmasi Tutup Buku {{ $info['period_key'] }}
                        </h3>
                    </div>

                    <button 
                        type="button" 
                        wire:click="closeClosingModal"
                        class="p-2 text-gray-400 hover:text-gray-700 dark:hover:text-white rounded-lg hover:bg-gray-200/50 dark:hover:bg-white/10 transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body Content --}}
                <div class="p-6 text-sm text-gray-700 dark:text-gray-300 space-y-4">
                    <p class="font-medium text-gray-900 dark:text-white">
                        Apakah Anda yakin ingin mengeksekusi Tutup Buku Bulanan untuk periode <span class="font-mono font-bold">{{ $info['period_key'] }}</span>?
                    </p>
                    <div class="p-3 bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-gray-800 rounded-lg text-xs space-y-1.5">
                        <div class="flex justify-between">
                            <span>Status Penguncian:</span>
                            <strong class="text-gray-900 dark:text-white">Dikunci (Locked)</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Jurnal Penutup:</span>
                            <strong class="font-mono text-gray-900 dark:text-white">#JV-CLOSE-{{ str_replace('-', '', $info['period_key']) }}</strong>
                        </div>
                        <div class="flex justify-between">
                            <span>Transfer Ke Laba Ditahan:</span>
                            <strong class="font-mono text-gray-900 dark:text-white">{{ \App\Helpers\FinancialReportHelper::formatRupiah($info['net_income']) }}</strong>
                        </div>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Seluruh penambahan dan perubahan data transaksi keuangan pada bulan ini akan dikunci dari penyuntingan harian.
                    </p>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 bg-gray-50/80 dark:bg-white/5 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                    <button 
                        type="button" 
                        wire:click="closeClosingModal"
                        class="px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 border border-gray-300 dark:border-gray-700 rounded-lg transition-all"
                    >
                        Batal
                    </button>

                    <button 
                        type="button" 
                        wire:click="executeClosing"
                        class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 rounded-lg shadow-md transition-all"
                    >
                        Ya, Eksekusi Tutup Buku
                    </button>
                </div>

            </div>
        </div>
    @endif

    {{-- INTERACTIVE POPUP MODAL DIALOG: KONFIRMASI BUKA KEMBALI PERIODE (REOPEN) --}}
    @if($this->showReopenModal && $reopenTarget)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm animate-fade-in">
            <div class="w-full max-w-md bg-white dark:bg-gray-900 border border-gray-200 dark:border-white/10 rounded-2xl shadow-2xl overflow-hidden flex flex-col">
                
                {{-- Modal Header --}}
                <div class="p-5 bg-gray-50/80 dark:bg-white/5 border-b border-gray-200 dark:border-white/10 flex justify-between items-center">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                        </svg>
                        <h3 class="text-base font-extrabold text-gray-900 dark:text-white">
                            Konfirmasi Buka Kembali {{ $reopenTarget->period_key }}
                        </h3>
                    </div>

                    <button 
                        type="button" 
                        wire:click="closeReopenModal"
                        class="p-2 text-gray-400 hover:text-gray-700 dark:hover:text-white rounded-lg hover:bg-gray-200/50 dark:hover:bg-white/10 transition-all"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>

                {{-- Modal Body Content --}}
                <div class="p-6 text-sm text-gray-700 dark:text-gray-300 space-y-4">
                    <p class="font-medium text-gray-900 dark:text-white">
                        Apakah Anda yakin ingin MEMBUKA KEMBALI periode <span class="font-mono font-bold">{{ $reopenTarget->period_key }}</span>?
                    </p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">
                        Jurnal Penutup otomatis <strong class="font-mono">{{ $reopenTarget->closingJournal?->entry_no ?? '-' }}</strong> akan dibatalkan, dan penguncian periode akan dibuka kembali untuk pengeditan.
                    </p>
                </div>

                {{-- Modal Footer --}}
                <div class="p-4 bg-gray-50/80 dark:bg-white/5 border-t border-gray-200 dark:border-white/10 flex justify-end gap-3">
                    <button 
                        type="button" 
                        wire:click="closeReopenModal"
                        class="px-4 py-2 text-xs font-bold text-gray-700 dark:text-gray-200 bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 border border-gray-300 dark:border-gray-700 rounded-lg transition-all"
                    >
                        Batal
                    </button>

                    <button 
                        type="button" 
                        wire:click="reopenPeriod"
                        class="px-4 py-2 text-xs font-bold text-white bg-red-600 hover:bg-red-700 dark:bg-red-600 dark:hover:bg-red-500 rounded-lg shadow-md transition-all"
                    >
                        Ya, Buka Kembali Periode
                    </button>
                </div>

            </div>
        </div>
    @endif
</x-filament-panels::page>
