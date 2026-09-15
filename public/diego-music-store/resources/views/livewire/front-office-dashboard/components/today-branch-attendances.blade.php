<!-- Dashboard Section: Presensi Karyawan Cabang Hari Ini (Owner Only) -->
<div class="space-y-3">
    <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200/80 dark:border-slate-700 shadow-sm overflow-hidden transition-colors">
        
        <!-- Section Header -->
        <div class="p-6 border-b border-slate-100 dark:border-slate-700/80 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 dark:bg-blue-950/50 text-primary dark:text-blue-400 flex items-center justify-center flex-shrink-0">
                    <i class="ph-bold ph-calendar-check text-xl"></i>
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-900 dark:text-slate-100">
                        Presensi Karyawan Cabang Hari Ini
                    </h2>
                    <p class="text-xs text-slate-400 dark:text-slate-500">
                        {{ $currentBranch ? $currentBranch->name : 'Semua Cabang' }} &bull; {{ now()->translatedFormat('l, d F Y') }}
                    </p>
                </div>
            </div>

            <!-- Summary Badges & Quick Action -->
            <div class="flex flex-wrap items-center gap-2 text-xs font-extrabold">
                <x-pos.utility.pill variant="default" size="sm">
                    Total: {{ $totalStaff }} Staf
                </x-pos.utility.pill>
                <x-pos.utility.pill variant="success" size="sm">
                    Hadir: {{ $hadirCount }}
                </x-pos.utility.pill>
                <x-pos.utility.pill variant="warning" size="sm">
                    Izin/Off: {{ $izinCount }}
                </x-pos.utility.pill>
                <x-pos.utility.pill variant="danger" size="sm">
                    Belum Presensi: {{ $belumCount }}
                </x-pos.utility.pill>

                <a href="{{ route('pos.attendances') }}" class="px-3.5 py-1.5 bg-primary hover:bg-primaryDark text-white rounded-xl shadow-sm transition inline-flex items-center gap-1.5 ml-2 cursor-pointer font-extrabold text-xs">
                    <span>Kelola Presensi</span>
                    <i class="ph-bold ph-arrow-right text-xs"></i>
                </a>
            </div>
        </div>

        <!-- Employees Attendance Table -->
        <x-pos.table.container class="!border-0 !shadow-none !rounded-none">
            <x-pos.table>
                <thead class="bg-slate-50/80 dark:bg-slate-900/40 text-slate-400 dark:text-slate-500 uppercase text-[10px] font-black tracking-wider border-b border-slate-100 dark:border-slate-700/60">
                    <tr>
                        <x-pos.table.th>Karyawan</x-pos.table.th>
                        <x-pos.table.th>Status Hari Ini</x-pos.table.th>
                        <x-pos.table.th>Jam Masuk / Pulang</x-pos.table.th>
                        <x-pos.table.th>Selfie &amp; Radius GPS</x-pos.table.th>
                        <x-pos.table.th>Kuota Off-Day</x-pos.table.th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50 text-xs font-semibold">
                    @forelse ($branchEmployees as $emp)
                        @php
                            $att = $emp->attendances->first();
                            $status = $att ? $att->status : 'belum';
                            $usedOff = $emp->used_off_days_this_month;
                            $quotaOff = $emp->monthly_off_days_quota;
                            $isOver = $emp->is_off_days_over_quota;
                        @endphp
                        <x-pos.table.tr>
                            <x-pos.table.td>
                                <div class="font-extrabold text-slate-800 dark:text-slate-100">{{ $emp->name }}</div>
                                <div class="text-[10px] text-slate-400 ">{{ $emp->nik }} &bull; {{ $emp->user?->roles->first()?->name ?? 'Kasir' }}</div>
                            </x-pos.table.td>
                            <x-pos.table.td>
                                @if ($status === 'hadir')
                                    <x-pos.utility.pill variant="success" size="xs">
                                        <i class="ph-bold ph-check-circle mr-1"></i> Hadir
                                    </x-pos.utility.pill>
                                @elseif ($status === 'belum')
                                    <x-pos.utility.pill variant="danger" size="xs">
                                        <i class="ph-bold ph-clock-afternoon mr-1"></i> Belum Presensi
                                    </x-pos.utility.pill>
                                @else
                                    <x-pos.utility.pill variant="warning" size="xs">
                                        {{ str_replace('_', ' ', $status) }}
                                    </x-pos.utility.pill>
                                @endif
                            </x-pos.table.td>
                            <x-pos.table.td class=" text-slate-600 dark:text-slate-300">
                                @if ($att && $att->clock_in)
                                    <div class="font-bold text-slate-800 dark:text-slate-100">In: {{ $att->clock_in->format('H:i') }}</div>
                                    <div class="text-[10px] text-slate-400">Out: {{ $att->clock_out ? $att->clock_out->format('H:i') : '-' }}</div>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </x-pos.table.td>
                            <x-pos.table.td>
                                @if ($att && $att->clock_in_photo_path)
                                    <div class="flex items-center gap-2">
                                        <img src="{{ Storage::url($att->clock_in_photo_path) }}" alt="Selfie" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700">
                                        <span class="text-[10px]  text-emerald-600 dark:text-emerald-400 font-bold">
                                            {{ number_format($att->distance_meters ?? 0, 0) }}m Radius
                                        </span>
                                    </div>
                                @else
                                    <span class="text-slate-400 text-[11px] font-normal">Tidak Ada Foto</span>
                                @endif
                            </x-pos.table.td>
                            <x-pos.table.td>
                                <span class="{{ $isOver ? 'text-rose-600 dark:text-rose-400 font-black' : 'text-slate-600 dark:text-slate-300' }}">
                                    {{ $usedOff }} / {{ $quotaOff }} Hari
                                </span>
                                @if ($isOver)
                                    <span class="ml-1 text-[9px] bg-rose-500 text-white font-extrabold px-1.5 py-0.5 rounded-full uppercase">OVER!</span>
                                @endif
                            </x-pos.table.td>
                        </x-pos.table.tr>
                    @empty
                        <x-pos.table.empty colspan="5" icon="ph-users" message="Belum ada staf karyawan terdaftar di cabang ini" />
                    @endforelse
                </tbody>
            </x-pos.table>
            <x-pos.table.footer :total="count($branchEmployees ?? [])" />
        </x-pos.table.container>
    </div>
</div>
