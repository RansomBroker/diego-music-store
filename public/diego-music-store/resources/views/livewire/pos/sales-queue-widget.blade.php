<div 
    wire:poll.8s 
    class="relative flex items-stretch flex-shrink-0 select-none"
>
    {{-- ==================== TAB ANTRIAN & MELAYANI TRIGGER STRIP ==================== --}}
    <div class="flex items-stretch bg-slate-100 dark:bg-slate-900/90 border border-slate-200 dark:border-slate-800 rounded-xl p-1 gap-1 shadow-xs flex-shrink-0">
        <!-- TAB 1: ANTRIAN -->
        <button 
            type="button" 
            wire:click="openWithTab('queue')"
            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all cursor-pointer {{ $isExpanded && $activeTab === 'queue' ? 'bg-primary text-white shadow-sm ring-2 ring-primary/30' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:text-primary dark:hover:text-blue-400 hover:bg-slate-50 dark:hover:bg-slate-750 border border-slate-200/80 dark:border-slate-700/80' }}"
            title="Klik untuk membuka Tab Antrian Sales"
        >
            <div class="w-6 h-6 rounded-md flex items-center justify-center {{ $isExpanded && $activeTab === 'queue' ? 'bg-white/20 text-white' : 'bg-blue-50 dark:bg-blue-500/20 text-primary dark:text-blue-400' }}">
                <i class="ph-bold ph-queue text-xs"></i>
            </div>
            <div class="flex flex-col text-left leading-tight min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-black uppercase tracking-wider">Antrian</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black {{ $isExpanded && $activeTab === 'queue' ? 'bg-white text-primary' : 'bg-primary text-white' }}">
                        {{ $waitingMembers->count() }}
                    </span>
                </div>
                <span class="text-[10px] font-semibold truncate max-w-[85px] sm:max-w-[100px] mt-0.5 {{ $isExpanded && $activeTab === 'queue' ? 'text-white/90' : 'text-slate-500 dark:text-slate-400' }}">
                    @if ($nextSales)
                        NEXT: {{ $nextSales->user->name }}
                    @else
                        Tidak ada antrian
                    @endif
                </span>
            </div>
        </button>

        <!-- TAB 2: MELAYANI -->
        <button 
            type="button" 
            wire:click="openWithTab('on_duty')"
            class="flex items-center gap-2 px-3 py-1.5 rounded-lg transition-all cursor-pointer {{ $isExpanded && $activeTab === 'on_duty' ? 'bg-emerald-600 text-white shadow-sm ring-2 ring-emerald-600/30' : 'bg-white dark:bg-slate-800 text-slate-700 dark:text-slate-200 hover:text-emerald-600 dark:hover:text-emerald-400 hover:bg-slate-50 dark:hover:bg-slate-750 border border-slate-200/80 dark:border-slate-700/80' }}"
            title="Klik untuk membuka Tab Sales Sedang Melayani"
        >
            <div class="w-6 h-6 rounded-md flex items-center justify-center {{ $isExpanded && $activeTab === 'on_duty' ? 'bg-white/20 text-white' : 'bg-emerald-50 dark:bg-emerald-500/20 text-emerald-600 dark:text-emerald-400' }}">
                <span class="w-2 h-2 rounded-full bg-emerald-500 {{ $onDutyMembers->isNotEmpty() ? 'animate-ping' : '' }}"></span>
            </div>
            <div class="flex flex-col text-left leading-tight min-w-0">
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-black uppercase tracking-wider">Melayani</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[9px] font-black {{ $isExpanded && $activeTab === 'on_duty' ? 'bg-white text-emerald-700' : 'bg-emerald-600 text-white' }}">
                        {{ $onDutyMembers->count() }}
                    </span>
                </div>
                <span class="text-[10px] font-semibold truncate max-w-[85px] sm:max-w-[100px] mt-0.5 {{ $isExpanded && $activeTab === 'on_duty' ? 'text-white/90' : 'text-slate-500 dark:text-slate-400' }}">
                    @if ($onDutyMembers->isNotEmpty())
                        {{ $onDutyMembers->first()->user->name }}@if ($onDutyMembers->count() > 1) (+{{ $onDutyMembers->count() - 1 }})@endif
                    @else
                        Belum ada aktif
                    @endif
                </span>
            </div>
        </button>
    </div>

    {{-- ==================== DROPDOWN POPOVER PANEL (JIKA KASIR KLIK DETAIL / TITIK TIGA) ==================== --}}
    @if ($isExpanded)
        <div 
            class="absolute top-full left-0 mt-2 w-88 sm:w-96 bg-white dark:bg-slate-900 backdrop-blur-md border border-slate-200 dark:border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[75vh] z-50 transition-all animate-in fade-in slide-in-from-top-2 duration-150"
            @click.away="$wire.isExpanded ? $wire.toggleExpanded() : null"
        >
            {{-- Header Modal --}}
            <div class="px-3 py-2 bg-slate-50 dark:bg-slate-950 text-slate-900 dark:text-white flex items-center justify-between border-b border-slate-200 dark:border-slate-800 transition-colors">
                <div class="flex items-center gap-2">
                    @if ($activeTab === 'queue')
                        <i class="ph-bold ph-queue text-primary dark:text-blue-400 text-sm"></i>
                        <span class="font-extrabold text-xs">Antrian Sales</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-primary/10 text-primary dark:text-blue-400 font-bold">
                            {{ $waitingMembers->count() }}
                        </span>
                    @else
                        <span class="w-2 h-2 rounded-full bg-emerald-500 {{ $onDutyMembers->isNotEmpty() ? 'animate-ping' : '' }}"></span>
                        <span class="font-extrabold text-xs">Sedang Melayani</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 dark:bg-emerald-950/60 text-emerald-700 dark:text-emerald-400 font-bold">
                            {{ $onDutyMembers->count() }}
                        </span>
                    @endif
                </div>

                <div class="flex items-center gap-1">
                    <button 
                        type="button" 
                        wire:click="syncRoster" 
                        class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-lg hover:bg-slate-200/60 dark:hover:bg-white/10 transition-colors cursor-pointer" 
                        title="Sinkronkan Anggota Sales Cabang"
                    >
                        <i class="ph-bold ph-arrows-clockwise text-xs"></i>
                    </button>
                    <button 
                        type="button" 
                        wire:click="toggleExpanded" 
                        class="p-1.5 text-slate-400 hover:text-slate-700 dark:hover:text-white rounded-lg hover:bg-slate-200/60 dark:hover:bg-white/10 transition-colors cursor-pointer"
                        title="Tutup Panel"
                    >
                        <i class="ph-bold ph-x text-xs"></i>
                    </button>
                </div>
            </div>

            {{-- Body List --}}
            <div class="flex-1 overflow-y-auto p-2.5 space-y-2 max-h-[320px] no-scrollbar bg-white dark:bg-slate-900">
                {{-- TAB 1: WAITING QUEUE --}}
                @if ($activeTab === 'queue')
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
                                class="p-2 rounded-xl border transition-all flex items-center justify-between gap-2 {{ $isNext ? 'bg-primary/5 dark:bg-primary/10 border-primary/40 dark:border-primary/50 shadow-xs' : 'bg-slate-50 dark:bg-slate-800/70 border-slate-200 dark:border-slate-800' }}"
                            >
                                <div class="flex items-center gap-2 min-w-0">
                                    {{-- Order Badge --}}
                                    <div class="w-6 h-6 rounded-lg flex items-center justify-center font-black text-xs flex-shrink-0 {{ $isNext ? 'bg-primary text-white shadow-xs shadow-primary/30' : 'bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-200' }}">
                                        {{ $member->queue_order }}
                                    </div>

                                    <div class="flex items-center gap-1.5 min-w-0">
                                        <span class="font-extrabold text-xs text-slate-900 dark:text-slate-100 truncate">
                                            {{ $member->user->name }}
                                        </span>
                                        @if ($isNext)
                                            <span class="px-1.5 py-0.2 rounded-md bg-amber-500 text-white text-[9px] font-black uppercase tracking-wider animate-pulse flex-shrink-0">
                                                NEXT
                                            </span>
                                        @endif
                                    </div>
                                </div>

                                <div class="flex items-center gap-1.5 flex-shrink-0">
                                    {{-- Tombol Mulai Melayani --}}
                                    <button 
                                        type="button" 
                                        wire:click="startDuty({{ $member->id }})"
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

                {{-- TAB 2: ON DUTY --}}
                @elseif ($activeTab === 'on_duty')
                    @if ($onDutyMembers->isEmpty())
                        <div class="py-6 text-center text-slate-400 dark:text-slate-500">
                            <p class="text-xs font-semibold">Tidak ada sales yang sedang melayani.</p>
                        </div>
                    @else
                        @foreach ($onDutyMembers as $member)
                            <div 
                                class="p-2 rounded-xl border border-emerald-200 dark:border-emerald-500/30 bg-emerald-50/70 dark:bg-emerald-950/20 transition-all flex items-center justify-between gap-2"
                            >
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse flex-shrink-0"></span>
                                    <div class="min-w-0">
                                        <div class="font-extrabold text-xs text-slate-900 dark:text-slate-100 truncate">
                                            {{ $member->user->name }}
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
                                    {{-- Selesai Melayani --}}
                                    <button 
                                        type="button" 
                                        wire:click="completeDuty({{ $member->id }})"
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
                @endif
            </div>
        </div>
    @endif
</div>
