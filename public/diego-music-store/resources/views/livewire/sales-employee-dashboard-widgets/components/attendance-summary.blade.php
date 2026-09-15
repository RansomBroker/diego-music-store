<!-- 8. Info & Bar Absensi Staf -->
<div class="bg-slate-50 dark:bg-slate-800/80 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 space-y-4">
    <div>
        <h3 class="text-sm font-extrabold text-slate-900 dark:text-white flex items-center gap-2">
            <i class="ph-bold ph-user-check text-blue-500 dark:text-blue-400"></i> Info & Bar Absensi Staf
        </h3>
        <p class="text-[11px] text-slate-500 dark:text-slate-400">Ringkasan kehadiran & sisa kuota off-day bulan ini</p>
    </div>

    <div class="p-4 rounded-xl bg-white dark:bg-slate-900 border border-slate-200/80 dark:border-slate-700/60 space-y-3">
        <div class="flex items-center justify-between text-xs font-extrabold">
            <span class="text-slate-600 dark:text-slate-300">Total Kehadiran:</span>
            <span class="text-emerald-600 dark:text-emerald-400  text-sm">{{ $attendanceInfo['total_hadir'] }} Hari</span>
        </div>

        <div class="space-y-1">
            <div class="flex justify-between text-[10px] font-bold text-slate-400">
                <span>Tingkat Kehadiran</span>
                <span>{{ $attendanceInfo['attendance_percent'] }}%</span>
            </div>
            <div class="w-full h-2 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full transition-all duration-500" style="width: {{ $attendanceInfo['attendance_percent'] }}%;"></div>
            </div>
        </div>

        <div class="border-t border-slate-100 dark:border-slate-800 pt-2 flex items-center justify-between text-xs font-semibold">
            <span class="text-slate-500 dark:text-slate-400">Kuota Off-Day Terpakai:</span>
            <span class="{{ $attendanceInfo['is_over_quota'] ? 'text-rose-600 dark:text-rose-400 font-black' : 'text-slate-800 dark:text-slate-200 font-bold' }}">
                {{ $attendanceInfo['used_off_days'] }} / {{ $attendanceInfo['monthly_off_days_quota'] }} Hari
            </span>
        </div>

        @if ($attendanceInfo['is_over_quota'])
            <div class="p-2 bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 border border-rose-200 dark:border-rose-800 rounded-lg text-[10px] font-black uppercase text-center">
                ⚠️ Terdeteksi Melebihi Kuota Off-Day!
            </div>
        @endif
    </div>
</div>
