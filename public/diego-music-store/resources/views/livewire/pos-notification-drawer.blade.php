<div x-data="{ open: {{ $unreadCount > 0 ? 'true' : 'false' }}, activeTab: 'produk' }"
     @open-notifications.window="open = true"
     @keydown.escape.window="open = false"
     class="relative z-[100]"
     x-cloak
     wire:poll.15s>
    
    <!-- Backdrop -->
    <div x-show="open"
         x-transition:enter="ease-in-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in-out duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="open = false"
         class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity">
    </div>

    <!-- Slide-over panel -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute inset-0 overflow-hidden">
            <div class="pointer-events-none fixed inset-y-0 right-0 flex max-w-full pl-10 sm:pl-16">
                <div x-show="open"
                     @click.away="open = false"
                     x-transition:enter="transform transition ease-in-out duration-300"
                     x-transition:enter-start="translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transform transition ease-in-out duration-300"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="translate-x-full"
                     class="pointer-events-auto w-screen max-w-sm sm:max-w-md flex flex-col bg-white dark:bg-slate-900 shadow-2xl">
                    
                    <!-- Panel Header -->
                    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-800/50">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                                <i class="ph-fill ph-bell text-xl"></i>
                            </div>
                            <div>
                                <h2 class="text-base font-black text-slate-900 dark:text-white leading-tight">Pusat Notifikasi</h2>
                                <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400">Tugas & Peringatan Sistem</p>
                            </div>
                        </div>
                        <button @click="open = false" class="text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors p-2 rounded-xl hover:bg-slate-200 dark:hover:bg-slate-700 cursor-pointer">
                            <i class="ph-bold ph-x text-lg"></i>
                        </button>
                    </div>

                    <!-- Tabs Header -->
                    <div class="px-5 pt-3 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
                        <div class="flex gap-4">
                            <button @click="activeTab = 'produk'" 
                                    :class="activeTab === 'produk' ? 'border-primary text-primary dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                                    class="pb-3 border-b-2 font-bold text-sm flex items-center gap-2 transition-colors cursor-pointer">
                                <i class="ph-bold ph-package text-base"></i> Produk ({{ $productNotifications->count() }})
                            </button>
                            <button @click="activeTab = 'tugas'" 
                                    :class="activeTab === 'tugas' ? 'border-primary text-primary dark:border-blue-400 dark:text-blue-400' : 'border-transparent text-slate-500 hover:text-slate-700 dark:text-slate-400 dark:hover:text-slate-200'"
                                    class="pb-3 border-b-2 font-bold text-sm flex items-center gap-2 transition-colors cursor-pointer">
                                <i class="ph-bold ph-clipboard-text text-base"></i> Tugas ({{ $taskNotifications->count() }})
                            </button>
                        </div>
                        
                        @if($unreadCount > 0)
                            <button wire:click="markAllAsRead" class="pb-3 text-xs font-bold text-primary dark:text-blue-400 hover:underline">
                                Tandai Semua Dibaca
                            </button>
                        @endif
                    </div>

                    <!-- Panel Body (Scrollable) -->
                    <div class="flex-1 overflow-y-auto p-5 no-scrollbar bg-slate-50/50 dark:bg-slate-900/50 relative">
                        
                        <!-- TAB: PRODUK -->
                        <div x-show="activeTab === 'produk'" class="space-y-3" x-transition.opacity>
                            @forelse($productNotifications as $notif)
                                <div class="p-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-emerald-500"></div>
                                    <div class="pl-2">
                                        <div class="flex items-start justify-between gap-2 mb-1">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">{{ $notif->data['title'] ?? 'Produk Baru' }}</h4>
                                            <span class="text-[9px] font-extrabold text-emerald-600 bg-emerald-50 dark:bg-emerald-900/40 px-2 py-0.5 rounded-full flex-shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug mb-2">{{ $notif->data['message'] ?? '' }}</p>
                                        <div class="flex items-center justify-between mt-3 pt-3 border-t border-slate-100 dark:border-slate-700/50">
                                            <a href="{{ $notif->data['action_url'] ?? '#' }}" class="text-[11px] font-bold text-slate-500 hover:text-primary dark:hover:text-blue-400 flex items-center gap-1 transition-colors">
                                                <i class="ph-bold ph-eye"></i> Lihat Produk
                                            </a>
                                            <button wire:click="markAsRead('{{ $notif->id }}')" class="text-[11px] font-black text-primary dark:text-blue-400 hover:underline cursor-pointer">Tandai Dibaca &check;</button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 opacity-60">
                                    <i class="ph-duotone ph-check-circle text-4xl text-slate-400 mb-2"></i>
                                    <p class="text-xs font-bold text-slate-500">Tidak ada notifikasi produk baru.</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- TAB: TUGAS -->
                        <div x-show="activeTab === 'tugas'" class="space-y-3" x-transition.opacity style="display: none;">
                            @forelse($taskNotifications as $notif)
                                <div class="p-3.5 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-2xl shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
                                    <div class="absolute left-0 top-0 bottom-0 w-1.5 bg-amber-500"></div>
                                    <div class="pl-2">
                                        <div class="flex items-start justify-between gap-2 mb-1">
                                            <h4 class="text-sm font-bold text-slate-900 dark:text-white leading-tight">{{ $notif->data['title'] ?? 'Tugas Baru' }}</h4>
                                            <span class="text-[9px] font-extrabold text-slate-500 bg-slate-100 dark:bg-slate-700 px-2 py-0.5 rounded-full flex-shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                                        </div>
                                        <p class="text-xs text-slate-600 dark:text-slate-400 leading-snug mb-2">{{ $notif->data['message'] ?? '' }}</p>
                                        <div class="text-right mt-2">
                                            @if(isset($notif->data['task_id']))
                                                <button wire:click="completeTask('{{ $notif->id }}', '{{ $notif->data['task_id'] }}')" class="text-[11px] font-black text-primary dark:text-blue-400 hover:underline cursor-pointer">Selesai &check;</button>
                                            @else
                                                <button wire:click="markAsRead('{{ $notif->id }}')" class="text-[11px] font-black text-primary dark:text-blue-400 hover:underline cursor-pointer">Tandai Dibaca &check;</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-10 opacity-60">
                                    <i class="ph-duotone ph-check-circle text-4xl text-slate-400 mb-2"></i>
                                    <p class="text-xs font-bold text-slate-500">Tidak ada tugas baru.</p>
                                </div>
                            @endforelse
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
