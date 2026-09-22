{{-- ===================== MODAL DETAIL: PROGRESS ANGGOTA GRUP ===================== --}}
<x-pos.modal
    wire:model="showGroupDetailModal"
    title="Detail Performa & Target Anggota Grup"
    subtitle="Pantau realisasi penjualan tiap staf dan status pembuka komisi grup"
    icon="ph-chart-bar"
    maxWidth="3xl"
>
    @if ($selectedGroupEvaluation)
        <div class="space-y-4">
            <!-- Header Summary Card -->
            <div class="p-4 rounded-xl bg-slate-50 dark:bg-slate-850 border border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <div class="text-xs text-slate-400">Grup Penjualan:</div>
                    <div class="font-extrabold text-base text-slate-900 dark:text-white">
                        {{ $selectedGroupEvaluation['group_name'] }}
                    </div>
                    <div class="text-xs text-slate-500 mt-0.5">
                        Leader: <strong>{{ $selectedGroupEvaluation['leader_name'] }}</strong> • Rate: <strong>{{ number_format($selectedGroupEvaluation['rate'], 2) }}%</strong>
                    </div>
                </div>

                <div class="text-left sm:text-right">
                    <div class="text-xs text-slate-400">Status Pembuka Komisi:</div>
                    <div class="mt-1">
                        @if ($selectedGroupEvaluation['is_unlocked'])
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-600 text-white shadow-sm">
                                <i class="ph-bold ph-lock-key-open text-xs"></i>
                                TERBUKA (100% Achieve)
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-300 border border-amber-300 dark:border-amber-800/40">
                                <i class="ph-bold ph-lock-key text-xs"></i>
                                TERKUNCI ({{ $selectedGroupEvaluation['achieved_members_count'] }}/{{ $selectedGroupEvaluation['total_members'] }} Tercapai)
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Omzet & Komisi Metrics -->
            <div class="grid grid-cols-2 gap-3">
                <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <span class="text-xs text-slate-400">Total Penjualan Grup</span>
                    <div class="text-base font-bold text-slate-900 dark:text-white mt-0.5">
                        Rp {{ number_format($selectedGroupEvaluation['total_group_sales'], 0, ',', '.') }}
                    </div>
                </div>
                <div class="p-3 rounded-lg bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800">
                    <span class="text-xs text-slate-400">Komisi Grup Leader</span>
                    <div class="text-base font-extrabold {{ $selectedGroupEvaluation['is_unlocked'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }} mt-0.5">
                        @if ($selectedGroupEvaluation['is_unlocked'])
                            Rp {{ number_format($selectedGroupEvaluation['commission_amount'], 0, ',', '.') }}
                        @else
                            Rp 0 (Terkunci)
                        @endif
                    </div>
                </div>
            </div>

            <!-- Tabel Detail Anggota -->
            <div class="border border-slate-200 dark:border-slate-800 rounded-xl overflow-hidden">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-300 font-bold border-b border-slate-200 dark:border-slate-800">
                        <tr>
                            <th class="px-3 py-2.5">Anggota Tim</th>
                            <th class="px-3 py-2.5 text-right">Target Bulanan</th>
                            <th class="px-3 py-2.5 text-right">Realisasi Penjualan</th>
                            <th class="px-3 py-2.5 text-center">% Capaian</th>
                            <th class="px-3 py-2.5 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
                        @foreach ($selectedGroupEvaluation['members_detail'] as $mem)
                            <tr class="{{ $mem['is_achieved'] ? 'bg-white dark:bg-slate-900' : 'bg-rose-50/30 dark:bg-rose-950/10' }}">
                                <td class="px-3 py-2.5">
                                    <div class="font-bold text-slate-900 dark:text-white">{{ $mem['name'] }}</div>
                                    <div class="text-[10px] text-slate-400">{{ $mem['nik'] }}</div>
                                </td>
                                <td class="px-3 py-2.5 text-right font-mono font-semibold text-slate-800 dark:text-slate-200">
                                    Rp {{ number_format($mem['target_sales'], 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2.5 text-right font-mono font-bold {{ $mem['is_achieved'] ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-700 dark:text-slate-300' }}">
                                    Rp {{ number_format($mem['actual_sales'], 0, ',', '.') }}
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    <span class="inline-flex px-2 py-0.5 rounded text-[11px] font-bold {{ $mem['is_achieved'] ? 'bg-emerald-100 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-400' }}">
                                        {{ $mem['achievement_pct'] }}%
                                    </span>
                                </td>
                                <td class="px-3 py-2.5 text-center">
                                    @if ($mem['is_achieved'])
                                        <span class="inline-flex items-center gap-1 text-emerald-600 dark:text-emerald-400 font-bold">
                                            <i class="ph-bold ph-check-circle text-sm"></i>
                                            Achieved
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-rose-500 font-semibold">
                                            <i class="ph-bold ph-x-circle text-sm"></i>
                                            Belum Capai
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Footer Action -->
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-200 dark:border-slate-800">
                <x-pos.utility.button
                    type="button"
                    variant="secondary"
                    wire:click="$set('showGroupDetailModal', false)"
                >
                    Tutup
                </x-pos.utility.button>

                @if ($selectedGroupEvaluation['is_unlocked'] && $selectedGroupEvaluation['commission_amount'] > 0)
                    <button
                        type="button"
                        wire:click="claimGroupCommission({{ $selectedGroupEvaluation['group_id'] }})"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 rounded-lg text-xs font-bold uppercase tracking-wider text-white bg-emerald-600 hover:bg-emerald-700 transition shadow-sm cursor-pointer"
                    >
                        <i class="ph-bold ph-check text-sm"></i>
                        Klaim Komisi Grup Ini
                    </button>
                @endif
            </div>
        </div>
    @endif
</x-pos.modal>
