<div class="flex h-screen min-h-dvh max-h-dvh w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- POS Global Toast Notification Listener -->
    <x-pos.toast />

    <!-- Left Navigation Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content Area -->
    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden">
        <!-- Header POS Global Navbar -->
        <x-pos.navbar
            pageTitle="Setting WhatsApp Fonnte"
            showBack="true"
            backLabel="Dashboard"
            backUrl="/pos/front-office"
        />

        <!-- Main Scrollable Body -->
        <div class="flex-1 overflow-y-auto p-4 sm:p-6 md:p-8 no-scrollbar space-y-6">
            
            <!-- Page Title & Header Bar -->
            <div class="bg-white dark:bg-slate-800 p-5 rounded-2xl border border-slate-200/80 dark:border-slate-700/80 shadow-xs">
                <div class="flex items-center gap-3.5">
                    <div class="w-12 h-12 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-2xl font-black flex-shrink-0">
                        <i class="ph-bold ph-whatsapp-logo"></i>
                    </div>
                    <div>
                        <h1 class="text-lg sm:text-xl font-black text-slate-900 dark:text-white tracking-tight">
                            Integrasi WhatsApp API Fonnte
                        </h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mt-0.5">
                            Konfigurasikan Token API Fonnte & Nomor Sender WhatsApp Pengirim per Cabang Toko
                        </p>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Settings Form Card -->
            <div class="bg-white dark:bg-slate-800 rounded-3xl p-6 border border-slate-200/80 dark:border-slate-700/80 shadow-xs space-y-6">
                
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 border-b border-slate-100 dark:border-slate-700/80 pb-4">
                    <div class="flex items-center gap-3">
                        <span class="w-3 h-3 rounded-full {{ $is_whatsapp_enabled ? 'bg-emerald-500 animate-pulse' : 'bg-slate-300 dark:bg-slate-600' }}"></span>
                        <h2 class="text-base font-black text-slate-900 dark:text-slate-100 tracking-tight">
                            Pengaturan WA — {{ $selectedBranch?->name }}
                        </h2>
                    </div>

                    <!-- Enable/Disable WhatsApp Toggle -->
                    <label class="flex items-center gap-3 cursor-pointer select-none">
                        <span class="text-xs font-bold text-slate-600 dark:text-slate-300">
                            Status WA Cabang: <strong class="{{ $is_whatsapp_enabled ? 'text-emerald-600 dark:text-emerald-400' : 'text-slate-400' }}">{{ $is_whatsapp_enabled ? 'Aktif' : 'Nonaktif' }}</strong>
                        </span>
                        <input
                            type="checkbox"
                            wire:model.live="is_whatsapp_enabled"
                            class="sr-only peer"
                        />
                        <div class="w-11 h-6 bg-slate-200 peer-focus:outline-none rounded-full peer dark:bg-slate-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:after:border-slate-600 peer-checked:bg-emerald-600 relative"></div>
                    </label>
                </div>

                <!-- Form Fields Grid -->
                <form wire:submit.prevent="save" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- Fonnte Token Field -->
                        <div class="space-y-2 md:col-span-2">
                            <label class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                <i class="ph-bold ph-key text-primary"></i>
                                <span>Fonnte API Token Cabang</span>
                            </label>
                            <div class="relative">
                                <input
                                    type="{{ $showTokenPassword ? 'text' : 'password' }}"
                                    wire:model="fonnte_token"
                                    placeholder="Masukkan Token Fonnte (misal: 12ab34cd56ef...)"
                                    class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-mono text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary focus:border-primary pr-12 transition"
                                />
                                <button
                                    type="button"
                                    wire:click="$toggle('showTokenPassword')"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 cursor-pointer"
                                    title="Tampilkan / Sembunyikan Token"
                                >
                                    <i class="ph-bold {{ $showTokenPassword ? 'ph-eye-slash' : 'ph-eye' }} text-lg"></i>
                                </button>
                            </div>
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                Token API unik perangkat Fonnte untuk cabang {{ $selectedBranch?->name }}. Dapatkan token di dasbor <a href="https://fonnte.com" target="_blank" class="text-primary hover:underline font-bold">fonnte.com</a>.
                            </p>
                            @error('fonnte_token')
                                <span class="text-xs text-rose-500 font-bold">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Sender WhatsApp Number -->
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                <i class="ph-bold ph-phone text-primary"></i>
                                <span>Nomor WhatsApp Sender Cabang</span>
                            </label>
                            <input
                                type="text"
                                wire:model="fonnte_whatsapp_number"
                                placeholder="Contoh: 081234567890"
                                class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-2xl text-sm font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary transition"
                            />
                            <p class="text-[11px] text-slate-400 dark:text-slate-500">
                                Nomor HP WhatsApp pengirim yang terhubung ke Fonnte device cabang ini.
                            </p>
                            @error('fonnte_whatsapp_number')
                                <span class="text-xs text-rose-500 font-bold">{{ $message }}</span>
                            @enderror
                        </div>

                        <!-- Device Connection Status Box -->
                        <div class="space-y-2">
                            <label class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                                <i class="ph-bold ph-broadcast text-primary"></i>
                                <span>Status Perangkat Fonnte</span>
                            </label>
                            <div class="p-3.5 bg-slate-50 dark:bg-slate-900/80 border border-slate-200 dark:border-slate-700 rounded-2xl flex items-center justify-between gap-3">
                                <div>
                                    @if ($device_status)
                                        <div class="flex items-center gap-2">
                                            <span class="w-2.5 h-2.5 rounded-full {{ $device_status['status'] ? 'bg-emerald-500 animate-pulse' : 'bg-rose-500' }}"></span>
                                            <span class="text-xs font-black text-slate-800 dark:text-slate-100">
                                                {{ $device_status['message'] }}
                                            </span>
                                        </div>
                                        @if (isset($device_status['quota']))
                                            <div class="text-[10px] text-slate-500 dark:text-slate-400 font-semibold mt-1">
                                                Sisa Kuota Fonnte: <strong class="text-emerald-600 dark:text-emerald-400">{{ number_format($device_status['quota'], 0, ',', '.') }} Pesan</strong>
                                            </div>
                                        @endif
                                        @if (isset($device_status['device_status']) && strtolower($device_status['device_status']) !== 'connect')
                                            <div class="mt-2 p-2.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-[11px] text-amber-700 dark:text-amber-400 font-semibold flex items-center gap-2">
                                                <i class="ph-bold ph-warning-circle text-base flex-shrink-0"></i>
                                                <span>WhatsApp terputus (disconnected). Buka dashboard Fonnte lalu scan QR ulang untuk menghubungkan ponsel.</span>
                                            </div>
                                        @endif
                                    @else
                                        <span class="text-xs font-bold text-slate-400">Klik tombol di kanan untuk cek koneksi device.</span>
                                    @endif
                                </div>
                                <button
                                    type="button"
                                    wire:click="checkStatus"
                                    wire:loading.attr="disabled"
                                    class="px-3 py-1.5 bg-slate-200 dark:bg-slate-700 hover:bg-slate-300 dark:hover:bg-slate-600 text-slate-700 dark:text-slate-200 text-xs font-bold rounded-xl transition flex items-center gap-1.5 flex-shrink-0 cursor-pointer"
                                >
                                    <i class="ph-bold ph-arrows-clockwise text-sm"></i>
                                    <span>Cek Device</span>
                                </button>
                            </div>
                        </div>

                    </div>

                    <!-- Action Buttons Toolbar -->
                    <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-slate-100 dark:border-slate-700/80">
                        <button
                            type="button"
                            wire:click="openTestModal"
                            class="w-full sm:w-auto px-5 py-3 bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs rounded-2xl shadow-sm hover:shadow-md transition flex items-center justify-center gap-2 cursor-pointer active:scale-95"
                        >
                            <i class="ph-bold ph-paper-plane-tilt text-base"></i>
                            <span>Test Koneksi WhatsApp Modal</span>
                        </button>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="w-full sm:w-auto px-6 py-3 bg-primary hover:bg-primary/90 text-white font-black text-xs rounded-2xl shadow-md hover:shadow-lg transition flex items-center justify-center gap-2 cursor-pointer active:scale-95"
                        >
                            <i class="ph-bold ph-floppy-disk text-base"></i>
                            <span>Simpan Pengaturan WA Cabang</span>
                        </button>
                    </div>
                </form>

            </div>

        </div>
    </main>

    <!-- Modal Test Koneksi WhatsApp -->
    @if ($showTestModal)
        <div class="fixed inset-0 bg-slate-950/60 backdrop-blur-xs z-50 flex items-center justify-center p-4" x-cloak>
            <div class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-2xl max-w-lg w-full p-6 space-y-5 animate-in fade-in zoom-in duration-200">
                
                <!-- Modal Header -->
                <div class="flex items-center justify-between border-b border-slate-100 dark:border-slate-700 pb-3">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center text-lg font-black">
                            <i class="ph-bold ph-paper-plane-tilt"></i>
                        </div>
                        <div>
                            <h3 class="text-sm font-black text-slate-900 dark:text-slate-100 tracking-tight">
                                Uji Coba Pengiriman WA — {{ $selectedBranch?->name }}
                            </h3>
                            <p class="text-[11px] text-slate-400 font-semibold">
                                Kirim pesan test WhatsApp secara langsung (synchronous)
                            </p>
                        </div>
                    </div>
                    <button
                        type="button"
                        wire:click="$set('showTestModal', false)"
                        class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 flex items-center justify-center transition cursor-pointer"
                    >
                        <i class="ph-bold ph-x text-base"></i>
                    </button>
                </div>

                <!-- Modal Form Body -->
                <form wire:submit.prevent="sendTestMessage" class="space-y-4">
                    <!-- Target Number Input -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="ph-bold ph-user-gear text-primary"></i>
                            <span>Nomor WhatsApp Tujuan Test</span>
                        </label>
                        <input
                            type="text"
                            wire:model="test_number"
                            placeholder="Contoh: 081234567890"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-bold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary"
                        />
                        @error('test_number')
                            <span class="text-xs text-rose-500 font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Test Message Textarea -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-black uppercase tracking-wider text-slate-600 dark:text-slate-300 flex items-center gap-1.5">
                            <i class="ph-bold ph-chat-text text-primary"></i>
                            <span>Isi Pesan Test</span>
                        </label>
                        <textarea
                            wire:model="test_message"
                            rows="4"
                            class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 rounded-xl text-xs font-semibold text-slate-800 dark:text-slate-100 focus:ring-2 focus:ring-primary"
                        ></textarea>
                        @error('test_message')
                            <span class="text-xs text-rose-500 font-bold">{{ $message }}</span>
                        @enderror
                    </div>

                    <!-- Result Banner Box -->
                    @if ($test_result)
                        <div class="p-3.5 rounded-2xl border text-xs {{ $test_result['status'] ? 'bg-emerald-50 dark:bg-emerald-950/40 border-emerald-200 text-emerald-800 dark:text-emerald-300' : 'bg-rose-50 dark:bg-rose-950/40 border-rose-200 text-rose-800 dark:text-rose-300' }} space-y-1">
                            <div class="flex items-center gap-2 font-black">
                                <i class="ph-bold {{ $test_result['status'] ? 'ph-check-circle text-emerald-600' : 'ph-x-circle text-rose-600' }} text-base"></i>
                                <span>{{ $test_result['status'] ? 'Berhasil Terkirim!' : 'Gagal Mengirim Pesan' }}</span>
                            </div>
                            <p class="font-semibold text-[11px] opacity-90">
                                {{ $test_result['message'] }}
                            </p>
                        </div>
                    @endif

                    <!-- Modal Actions -->
                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100 dark:border-slate-700">
                        <button
                            type="button"
                            wire:click="$set('showTestModal', false)"
                            class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 font-bold text-xs rounded-xl hover:bg-slate-200 transition cursor-pointer"
                        >
                            Batal
                        </button>

                        <button
                            type="submit"
                            wire:loading.attr="disabled"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-xs rounded-xl shadow-md transition flex items-center gap-2 cursor-pointer active:scale-95"
                        >
                            <span wire:loading.remove wire:target="sendTestMessage">Kirim Pesan Test</span>
                            <span wire:loading wire:target="sendTestMessage" class="flex items-center gap-1.5">
                                <i class="ph-bold ph-spinner animate-spin text-sm"></i>
                                <span>Mengirim...</span>
                            </span>
                        </button>
                    </div>
                </form>

            </div>
        </div>
    @endif
</div>
