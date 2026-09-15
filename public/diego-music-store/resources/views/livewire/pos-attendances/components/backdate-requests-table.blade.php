<!-- Tab: Permohonan Backdate -->
<div>
    <div class="px-6 py-3.5 border-b border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-800/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div class="flex items-center gap-2">
            <i class="ph-bold ph-hourglass text-amber-500 text-base"></i>
            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">
                Daftar permohonan presensi susulan (backdate) dari karyawan yang memerlukan verifikasi Owner / Admin.
            </span>
        </div>
        <span class="text-xs font-bold text-amber-700 dark:text-amber-400">
            Total: {{ count($pendingBackdateRequests) }} Permohonan Pending
        </span>
    </div>

    <x-pos.table.container>
        <x-pos.table>
            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium text-xs">
                <tr>
                    <x-pos.table.th>Tanggal Diminta</x-pos.table.th>
                    <x-pos.table.th>Karyawan</x-pos.table.th>
                    <x-pos.table.th>Cabang</x-pos.table.th>
                    <x-pos.table.th>Jam Diminta</x-pos.table.th>
                    <x-pos.table.th>Alasan Pengajuan</x-pos.table.th>
                    <x-pos.table.th>Bukti Foto</x-pos.table.th>
                    <x-pos.table.th>Status</x-pos.table.th>
                    <x-pos.table.th class="text-right">Aksi Verifikasi</x-pos.table.th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300 text-xs">
                @forelse ($pendingBackdateRequests as $req)
                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                        <x-pos.table.td class="font-semibold text-slate-900 dark:text-slate-100">
                            <span class="px-2 py-0.5 bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 rounded-md text-[11px] font-bold">
                                {{ $req->requested_date->format('d M Y') }}
                            </span>
                        </x-pos.table.td>
                        <x-pos.table.td class="font-bold text-slate-900 dark:text-slate-100">
                            {{ $req->employee->name ?? '-' }}
                            <div class="text-[10px] text-slate-400 font-mono">{{ $req->employee->nik ?? '' }}</div>
                        </x-pos.table.td>
                        <x-pos.table.td>
                            {{ $req->branch?->name ?: 'Cabang Utama' }}
                        </x-pos.table.td>
                        <x-pos.table.td>
                            <div class="space-y-0.5">
                                <div class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                    In: {{ substr($req->clock_in, 0, 5) }}
                                </div>
                                <div class="font-mono text-amber-600 dark:text-amber-400 font-bold">
                                    Out: {{ substr($req->clock_out ?: '17:00', 0, 5) }}
                                </div>
                            </div>
                        </x-pos.table.td>
                        <x-pos.table.td class="max-w-xs text-slate-600 dark:text-slate-300">
                            {{ $req->reason }}
                        </x-pos.table.td>
                        <x-pos.table.td>
                            @if ($req->proof_photo_path)
                                <a href="{{ \Illuminate\Support\Facades\Storage::url($req->proof_photo_path) }}" target="_blank" title="Foto Bukti">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($req->proof_photo_path) }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700 hover:scale-110 transition-transform">
                                </a>
                            @else
                                <span class="text-slate-400 text-[11px]">-</span>
                            @endif
                        </x-pos.table.td>
                        <x-pos.table.td>
                            <x-pos.utility.pill variant="warning" size="xs">
                                Pending
                            </x-pos.utility.pill>
                        </x-pos.table.td>
                        <x-pos.table.td class="text-right">
                            @if ($isOwnerUser)
                                <div class="flex items-center justify-end gap-1.5">
                                    <x-pos.utility.button
                                        variant="danger"
                                        size="sm"
                                        icon="ph-x"
                                        wire:click="processBackdateApproval({{ $req->id }}, 'reject')"
                                    >
                                        Tolak
                                    </x-pos.utility.button>
                                    <x-pos.utility.button
                                        variant="success"
                                        size="sm"
                                        icon="ph-check"
                                        wire:click="processBackdateApproval({{ $req->id }}, 'approve')"
                                    >
                                        Setujui
                                    </x-pos.utility.button>
                                </div>
                            @else
                                <span class="text-[11px] text-slate-400 italic">Menunggu Owner</span>
                            @endif
                        </x-pos.table.td>
                    </tr>
                @empty
                    <x-pos.table.empty colspan="8" icon="ph-check-circle" message="Tidak ada permohonan presensi susulan yang pending" />
                @endforelse
            </tbody>
        </x-pos.table>
        <x-pos.table.footer :total="count($pendingBackdateRequests)" />
    </x-pos.table.container>
</div>
