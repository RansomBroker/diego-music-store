<div
    wire:poll.30s.visible
    x-data="{
        open: @js((bool) $isExpanded),
        tab: @js($activeTab ?: 'queue'),
        openTab(target) {
            if (this.open && this.tab === target) {
                this.open = false;
            } else {
                this.tab = target;
                this.open = true;
            }
        }
    }"
    @keydown.escape.window="open = false"
    @click.outside="open = false"
    class="relative flex items-stretch flex-shrink-0 select-none w-full sm:w-auto"
>
    {{-- ==================== TAB ANTRIAN & MELAYANI TRIGGER STRIP ==================== --}}
    <div class="grid grid-cols-2 sm:flex items-stretch w-full sm:w-auto bg-slate-100 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl p-1 gap-1 shadow-xs flex-shrink-0">
        <!-- TAB 1: ANTRIAN -->
        <button
            type="button"
            @click="openTab('queue')"
            :class="open && tab === 'queue'
                ? 'bg-primary text-white shadow-sm ring-2 ring-primary/30 border-transparent'
                : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-750 border-slate-200/80 dark:border-slate-700/80'"
            class="flex items-center gap-2 px-2.5 xl:px-3 py-1.5 rounded-lg transition-all cursor-pointer border min-w-0"
            title="Klik untuk membuka Tab Antrian Sales"
        >
            <div
                :class="open && tab === 'queue'
                    ? 'bg-white/20 text-white'
                    : 'bg-blue-50 dark:bg-blue-500/20 text-primary dark:text-blue-400'"
                class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0"
            >
                <i class="ph-bold ph-queue text-xs"></i>
            </div>
            <div class="flex flex-col text-left leading-tight min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-black uppercase tracking-wider">Antrian</span>
                    <span
                        :class="open && tab === 'queue' ? 'bg-white text-primary' : 'bg-primary text-white'"
                        class="px-1.5 py-0.2 rounded-full text-[9px] font-black"
                    >
                        {{ $waitingMembers->count() }}
                    </span>
                </div>
                <span
                    :class="open && tab === 'queue' ? 'text-white/90' : 'text-slate-500 dark:text-slate-400'"
                    class="text-[10px] font-semibold truncate max-w-full sm:max-w-[80px] xl:max-w-[105px] mt-0.5"
                >
                    @if ($nextSales)
                        NEXT: {{ $nextSales->user?->name }}
                    @else
                        Tidak ada antrian
                    @endif
                </span>
            </div>
        </button>

        <!-- TAB 2: MELAYANI -->
        <button
            type="button"
            @click="openTab('on_duty')"
            :class="open && tab === 'on_duty'
                ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-600/30 border-transparent'
                : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-750 border-slate-200/80 dark:border-slate-700/80'"
            class="flex items-center gap-2 px-2.5 xl:px-3 py-1.5 rounded-lg transition-all cursor-pointer border min-w-0"
            title="Klik untuk membuka Tab Sales Sedang Melayani"
        >
            <div
                :class="open && tab === 'on_duty'
                    ? 'bg-white/20 text-white'
                    : 'bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400'"
                class="w-6 h-6 rounded-md flex items-center justify-center flex-shrink-0"
            >
                <span class="w-2 h-2 rounded-full bg-emerald-500" :class="open && tab === 'on_duty' ? 'bg-white' : 'bg-emerald-500'"></span>
            </div>
            <div class="flex flex-col text-left leading-tight min-w-0 flex-1">
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-black uppercase tracking-wider">Melayani</span>
                    <span
                        :class="open && tab === 'on_duty' ? 'bg-white text-emerald-700' : 'bg-emerald-600 text-white'"
                        class="px-1.5 py-0.2 rounded-full text-[9px] font-black"
                    >
                        {{ $onDutyMembers->count() }}
                    </span>
                </div>
                <span
                    :class="open && tab === 'on_duty' ? 'text-white/90' : 'text-slate-500 dark:text-slate-400'"
                    class="text-[10px] font-semibold truncate max-w-full sm:max-w-[80px] xl:max-w-[105px] mt-0.5"
                >
                    @if ($onDutyMembers->isNotEmpty())
                        {{ $onDutyMembers->first()->user?->name }}@if ($onDutyMembers->count() > 1) (+{{ $onDutyMembers->count() - 1 }})@endif
                    @else
                        Belum ada aktif
                    @endif
                </span>
            </div>
        </button>
    </div>

    {{-- ==================== MOBILE BACKDROP ==================== --}}
    <div
        x-show="open"
        x-cloak
        @click="open = false"
        class="fixed inset-0 bg-slate-900/40 z-40 sm:hidden"
    ></div>

    {{-- ==================== DROPDOWN POPOVER PANEL ==================== --}}
    <div
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        class="fixed sm:absolute inset-x-3 sm:inset-x-auto top-20 sm:top-full sm:left-0 sm:mt-2 sm:w-96 max-w-[calc(100vw-1.5rem)] bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[75vh] z-50"
    >
        {{-- Header Modal + Quick Tab Switcher --}}
        <div class="px-3 py-2 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white flex items-center justify-between gap-2 border-b border-slate-200 dark:border-slate-800 transition-colors">
            <div class="flex items-center gap-1 min-w-0">
                <button
                    type="button"
                    @click="tab = 'queue'"
                    :class="tab === 'queue'
                        ? 'bg-primary/10 text-primary dark:bg-blue-950/60 dark:text-blue-400'
                        : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
                    class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-extrabold transition-colors cursor-pointer"
                >
                    <i class="ph-bold ph-queue text-sm"></i>
                    <span>Antrian Sales</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-primary/15 text-primary dark:text-blue-400 font-bold">
                        {{ $waitingMembers->count() }}
                    </span>
                </button>
                <button
                    type="button"
                    @click="tab = 'on_duty'"
                    :class="tab === 'on_duty'
                        ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-400'
                        : 'text-slate-500 hover:text-slate-800 dark:text-slate-400 dark:hover:text-slate-200'"
                    class="flex items-center gap-1.5 px-2 py-1 rounded-lg text-xs font-extrabold transition-colors cursor-pointer"
                >
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Sedang Melayani</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-500/15 text-emerald-700 dark:text-emerald-400 font-bold">
                        {{ $onDutyMembers->count() }}
                    </span>
                </button>
            </div>

            <div class="flex items-center gap-1 flex-shrink-0">
                <button
                    type="button"
                    wire:click="syncRoster"
                    wire:loading.attr="disabled"
                    wire:target="syncRoster"
                    wire:loading.class="opacity-60 cursor-wait"
                    class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-lg hover:bg-slate-200/60 dark:hover:bg-white/10 transition-colors cursor-pointer"
                    title="Sinkronkan Anggota Sales Cabang"
                >
                    <i wire:loading.class="animate-spin" wire:target="syncRoster" class="ph-bold ph-arrows-clockwise text-xs"></i>
                </button>
                <button
                    type="button"
                    @click="open = false"
                    class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-lg hover:bg-slate-200/60 dark:hover:bg-white/10 transition-colors cursor-pointer"
                    title="Tutup Panel"
                >
                    <i class="ph-bold ph-x text-xs"></i>
                </button>
            </div>
        </div>

        {{-- Body List --}}
        <div class="flex-1 overflow-y-auto p-2.5 max-h-[320px] no-scrollbar bg-white dark:bg-slate-900">
            {{-- TAB 1: WAITING QUEUE --}}
            <div x-show="tab === 'queue'" class="space-y-2">
                @if ($waitingMembers->isEmpty())
                    <div class="py-6 text-center text-slate-400 dark:text-slate-500">
                        <p class="text-xs font-semibold">Tidak ada sales dalam antrian.</p>
                    </div>
                @else
                    @foreach ($waitingMembers as $index => $member)
                        @php
                            $isNext = $index === 0;
                        @endphp
                        <div
                            wire:key="queue-wait-{{ $member->id }}"
                            class="p-2 rounded-xl border transition-all flex items-center justify-between gap-2 {{ $isNext ? 'bg-primary/5 dark:bg-primary/10 border-primary/40 dark:border-primary/50 shadow-xs' : 'bg-slate-50 dark:bg-slate-800/70 border-slate-200 dark:border-slate-800' }}"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-6 h-6 rounded-lg flex items-center justify-center font-black text-xs flex-shrink-0 {{ $isNext ? 'bg-primary text-white shadow-xs shadow-primary/30' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' }}">
                                    {{ $member->queue_order }}
                                </div>

                                <div class="flex items-center gap-1.5 min-w-0">
                                    <span class="font-extrabold text-xs text-slate-900 dark:text-slate-100 truncate">
                                        {{ $member->user?->name }}
                                    </span>
                                    @if ($isNext)
                                        <span class="px-1.5 py-0.2 rounded-md bg-amber-500 text-white text-[9px] font-black uppercase tracking-wider flex-shrink-0">
                                            NEXT
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <button
                                    type="button"
                                    wire:click="startDuty({{ $member->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="startDuty({{ $member->id }})"
                                    wire:loading.class="opacity-60 cursor-wait"
                                    class="px-2.5 py-1.5 rounded-lg text-xs font-black transition-all flex items-center gap-1 cursor-pointer {{ $isNext ? 'bg-primary hover:bg-primaryHover text-white shadow-xs shadow-primary/25 active:scale-95' : 'bg-slate-200 hover:bg-slate-300 dark:bg-slate-700 dark:hover:bg-primary text-slate-700 dark:text-slate-200 dark:hover:text-white' }}"
                                    title="Tugaskan melayani pelanggan"
                                >
                                    <i class="ph-bold ph-handshake text-sm"></i>
                                    <span>Layani</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>

            {{-- TAB 2: ON DUTY --}}
            <div x-show="tab === 'on_duty'" x-cloak class="space-y-2">
                @if ($onDutyMembers->isEmpty())
                    <div class="py-6 text-center text-slate-400 dark:text-slate-500">
                        <p class="text-xs font-semibold">Tidak ada sales yang sedang melayani.</p>
                    </div>
                @else
                    @foreach ($onDutyMembers as $member)
                        <div
                            wire:key="queue-duty-{{ $member->id }}"
                            class="p-2 rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50/70 dark:bg-emerald-950/20 transition-all flex items-center justify-between gap-2"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 flex-shrink-0"></span>
                                <div class="min-w-0">
                                    <div class="font-extrabold text-xs text-slate-900 dark:text-slate-100 truncate">
                                        {{ $member->user?->name }}
                                    </div>
                                    @if ($member->currentDutyLog)
                                        <div class="text-[10px] text-slate-500 dark:text-slate-400 flex items-center gap-1 mt-0.5">
                                            <i class="ph-bold ph-clock text-[10px]"></i>
                                            <span>{{ $member->currentDutyLog->duration_text }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <button
                                    type="button"
                                    wire:click="completeDuty({{ $member->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="completeDuty({{ $member->id }})"
                                    wire:loading.class="opacity-60 cursor-wait"
                                    class="py-1.5 px-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold flex items-center justify-center gap-1.5 transition-all cursor-pointer shadow-xs active:scale-95"
                                    title="Selesai melayani dan kembali ke antrian belakang"
                                >
                                    <i class="ph-bold ph-check-circle text-xs"></i>
                                    <span>Selesai</span>
                                </button>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
