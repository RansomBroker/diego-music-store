<div class="flex h-screen min-h-dvh max-h-dvh w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <x-pos.toast />
    <x-pos-page::sidebar />

    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden">
        <x-pos.navbar pageTitle="Kalender Jadwal Off" backLabel="Dashboard" />

        <div class="flex-1 overflow-y-auto no-scrollbar p-4 sm:p-6 md:p-8">
            <div class="mx-auto w-full max-w-7xl space-y-6">
                <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h2 class="text-2xl font-black tracking-tight text-slate-900 dark:text-white">Kalender Jadwal Off</h2>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">Jadwal off cabang {{ $selectedBranch?->name ?? "belum dipilih" }}.</p>
                        <p class="mt-1 text-xs font-semibold text-slate-500 dark:text-slate-400">Kuota off bulanan per karyawan: {{ $monthlyOffQuota }} hari.</p>
                    </div>

                    <div class="flex items-center gap-2">
                        <button type="button" wire:click="previousMonth" @disabled($isCurrentMonth)
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            aria-label="Bulan sebelumnya">
                            <i class="ph-bold ph-caret-left"></i>
                        </button>
                        <div class="min-w-36 text-center text-base font-extrabold capitalize text-slate-900 dark:text-white">{{ $monthLabel }}</div>
                        <button type="button" wire:click="nextMonth" @disabled($isNextMonth)
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl border border-slate-200 bg-white text-slate-700 transition hover:bg-slate-100 disabled:cursor-not-allowed disabled:opacity-40 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            aria-label="Bulan berikutnya">
                            <i class="ph-bold ph-caret-right"></i>
                        </button>
                        <button type="button" wire:click="goToCurrentMonth"
                            class="ml-1 rounded-xl bg-primary px-4 py-2.5 text-sm font-bold text-white transition hover:opacity-90">
                            Hari Ini
                        </button>
                    </div>
                </div>

                @if (!$currentEmployee && !$isOwner)
                    <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200">
                        Akun Anda belum terhubung dengan profil karyawan aktif. Anda tetap dapat melihat kalender, tetapi tidak dapat mendaftarkan jadwal off.
                    </div>
                @endif

                <div class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50 dark:border-slate-800 dark:bg-slate-800/70">
                        @foreach (['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $weekday)
                            <div class="px-1 py-3 text-center text-[11px] font-black uppercase tracking-wider text-slate-500 sm:px-3 sm:text-xs">{{ $weekday }}</div>
                        @endforeach
                    </div>

                    <div class="grid grid-cols-7">
                        @foreach ($weeks as $week)
                            @foreach ($week as $day)
                                <div wire:key="calendar-day-{{ $day['date'] }}"
                                    class="min-h-24 border-b border-r border-slate-100 p-1.5 sm:min-h-32 sm:p-2.5 dark:border-slate-800 {{ !$day['isCurrentMonth'] ? 'bg-slate-50/70 dark:bg-slate-950/30' : 'bg-white dark:bg-slate-900' }}">
                                    <div class="mb-1 flex items-center justify-between gap-1">
                                        <span class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-bold {{ $day['isToday'] ? 'bg-primary text-white' : ($day['isCurrentMonth'] ? 'text-slate-800 dark:text-slate-200' : 'text-slate-400 dark:text-slate-600') }}">
                                            {{ $day['day'] }}
                                        </span>
                                        @if ($day['isSelectable'] && (($currentEmployee?->is_active && $currentEmployee?->branch_id) || $isOwner))
                                            <button type="button" wire:click="openDate('{{ $day['date'] }}')"
                                                class="flex h-7 w-7 items-center justify-center rounded-lg text-slate-400 transition hover:bg-primary/10 hover:text-primary dark:hover:bg-blue-950/60 dark:hover:text-blue-400"
                                                title="Tambah jadwal off">
                                                <i class="ph-bold ph-plus"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="space-y-1">
                                        @foreach ($day['activeDayOffs']->take(3) as $dayOff)
                                            <button type="button" wire:click="openDate('{{ $day['date'] }}')"
                                                wire:key="day-off-{{ $dayOff->id }}"
                                                class="block w-full truncate rounded-md bg-emerald-50 px-1.5 py-1 text-left text-[10px] font-bold text-emerald-800 ring-1 ring-inset ring-emerald-200/70 sm:text-xs dark:bg-emerald-950/50 dark:text-emerald-300 dark:ring-emerald-900">
                                                {{ $dayOff->employee->name }}
                                            </button>
                                        @endforeach
                                        @if ($day['activeDayOffs']->count() > 3)
                                            <button type="button" wire:click="openDate('{{ $day['date'] }}')"
                                                class="block w-full text-left text-[10px] font-bold text-primary hover:underline sm:text-xs">
                                                +{{ $day['activeDayOffs']->count() - 3 }} lainnya
                                            </button>
                                        @endif
                                        @if ($isOwner && $day['cancelledCount'] > 0)
                                            <div class="px-1 text-[10px] font-semibold text-slate-400">{{ $day['cancelledCount'] }} dibatalkan</div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-500 dark:text-slate-400">
                    <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-emerald-500"></span> Jadwal off aktif</span>
                    @if ($isOwner)
                        <span class="inline-flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-sm bg-slate-400"></span> Riwayat pembatalan tersedia untuk owner</span>
                    @endif
                </div>
            </div>
        </div>
    </main>

    @if ($showAddModal && $selectedDate)
        <div class="fixed inset-0 z-[70] flex items-center justify-center bg-slate-950/50 p-4" wire:click.self="closeModals">
            <section class="w-full max-w-lg overflow-hidden rounded-2xl bg-white shadow-2xl dark:bg-slate-900">
                <div class="flex items-start justify-between border-b border-slate-200 p-5 dark:border-slate-800">
                    <div>
                        <h3 class="text-lg font-black text-slate-900 dark:text-white">Jadwal Off</h3>
                        <p class="mt-1 text-sm text-slate-500 dark:text-slate-400">{{ \Carbon\Carbon::parse($selectedDate)->locale('id')->translatedFormat('l, d F Y') }}</p>
                    </div>
                    <button type="button" wire:click="closeModals" class="rounded-lg p-2 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800" aria-label="Tutup">
                        <i class="ph-bold ph-x"></i>
                    </button>
                </div>
                <div class="space-y-4 p-5">
                    <div class="space-y-2">
                        <h4 class="text-xs font-black uppercase tracking-wider text-slate-500">Karyawan yang off</h4>
                        @forelse ($selectedDayOffs->where('status', 'active') as $dayOff)
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 p-3 dark:border-slate-800">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-900 dark:text-white">{{ $dayOff->employee->name }}</p>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400">Aktif</p>
                                </div>
                                @if ($isOwner)
                                    <button type="button" wire:click="openCancelModal({{ $dayOff->id }})"
                                        class="shrink-0 rounded-lg border border-rose-200 px-3 py-2 text-xs font-bold text-rose-600 transition hover:bg-rose-50 dark:border-rose-900 dark:text-rose-300 dark:hover:bg-rose-950/40">
                                        Batalkan
                                    </button>
                                @endif
                            </div>
                        @empty
                            <p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500 dark:bg-slate-800">Belum ada karyawan yang terdaftar off pada tanggal ini.</p>
                        @endforelse

                        @if ($isOwner && $selectedDayOffs->where('status', 'cancelled')->isNotEmpty())
                            <div class="pt-2">
                                <h4 class="mb-2 text-xs font-black uppercase tracking-wider text-slate-500">Riwayat pembatalan</h4>
                                @foreach ($selectedDayOffs->where('status', 'cancelled') as $dayOff)
                                    <div class="mb-2 rounded-xl bg-slate-50 p-3 text-sm dark:bg-slate-800">
                                        <div class="font-bold text-slate-700 dark:text-slate-200">{{ $dayOff->employee->name }}</div>
                                        <div class="mt-1 text-xs text-slate-500">Dibatalkan {{ $dayOff->cancelled_at?->format('d/m/Y H:i') ?? '' }} oleh {{ $dayOff->canceller?->name ?? 'akun yang sudah tidak aktif' }}</div>
                                        @if ($dayOff->cancellation_reason)
                                            <div class="mt-1 text-xs text-slate-500">Alasan: {{ $dayOff->cancellation_reason }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    @if ($currentEmployee?->is_active && $currentEmployee?->branch_id && !$isOwner && !$selectedDayOffs->contains(fn ($item) => $item->employee_id === $currentEmployee->id && $item->status === 'active'))
                        <div class="border-t border-slate-200 pt-4 dark:border-slate-800">
                            <p class="mb-3 text-sm text-slate-500 dark:text-slate-400">Daftarkan diri Anda untuk off pada tanggal ini. Kuota bulanan cabang: {{ $monthlyOffQuota }} hari per karyawan.</p>
                            <button type="button" wire:click="registerMyself" wire:loading.attr="disabled"
                                class="w-full rounded-xl bg-primary px-4 py-3 text-sm font-extrabold text-white transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-50">
                                <span wire:loading.remove wire:target="registerMyself">Daftarkan Saya Off</span>
                                <span wire:loading wire:target="registerMyself">Menyimpan...</span>
                            </button>
                            @error('selectedDate') <p class="mt-2 text-xs font-semibold text-rose-600">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
            </section>
        </div>
    @endif

    @if ($showCancelModal)
        <div class="fixed inset-0 z-[80] flex items-center justify-center bg-slate-950/60 p-4" wire:click.self="closeModals">
            <section class="w-full max-w-md rounded-2xl bg-white p-5 shadow-2xl dark:bg-slate-900">
                <h3 class="text-lg font-black text-slate-900 dark:text-white">Batalkan Jadwal Off</h3>
                <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Hanya jadwal karyawan yang dipilih yang akan dibatalkan. Karyawan lain pada tanggal yang sama tidak terpengaruh.</p>
                <div class="mt-4">
                    <label for="cancellationReason" class="mb-1.5 block text-xs font-bold text-slate-600 dark:text-slate-300">Alasan pembatalan (opsional)</label>
                    <textarea id="cancellationReason" wire:model="cancellationReason" rows="3" maxlength="500"
                        class="w-full rounded-xl border border-slate-200 bg-white p-3 text-sm text-slate-900 outline-none focus:border-primary dark:border-slate-700 dark:bg-slate-800 dark:text-white"
                        placeholder="Masukkan alasan pembatalan..."></textarea>
                    @error('cancellationReason') <p class="mt-1 text-xs text-rose-600">{{ $message }}</p> @enderror
                </div>
                <div class="mt-5 flex justify-end gap-2">
                    <button type="button" wire:click="closeModals" class="rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 dark:border-slate-700 dark:text-slate-300">Kembali</button>
                    <button type="button" wire:click="cancelSelectedDayOff" wire:loading.attr="disabled"
                        class="rounded-xl bg-rose-600 px-4 py-2.5 text-sm font-extrabold text-white hover:bg-rose-700 disabled:opacity-50">
                        <span wire:loading.remove wire:target="cancelSelectedDayOff">Ya, Batalkan</span>
                        <span wire:loading wire:target="cancelSelectedDayOff">Memproses...</span>
                    </button>
                </div>
            </section>
        </div>
    @endif
</div>
