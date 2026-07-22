{{--
    Komponen POS Toast Notification Modern (Sudut Kanan Atas - Anti Duplicate)
    =======================================================================
--}}
<div
    x-data="posToastContainer()"
    x-init="initToasts()"
    class="fixed top-5 right-5 z-[99999] flex flex-col gap-3 max-w-sm w-full pointer-events-none"
    style="top: 1.25rem; right: 1.25rem; position: fixed;"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div
            x-show="toast.visible"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-y-[-10px] opacity-0 scale-95"
            x-transition:enter-end="translate-y-0 opacity-100 scale-100"
            x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="pointer-events-auto flex items-start gap-3.5 p-4 rounded-2xl shadow-2xl border backdrop-blur-md transition-all duration-200"
            :class="{
                'bg-emerald-600/95 dark:bg-emerald-900/95 border-emerald-400/50 text-white shadow-emerald-950/20': toast.type === 'success',
                'bg-rose-600/95 dark:bg-rose-900/95 border-rose-400/50 text-white shadow-rose-950/20': toast.type === 'danger' || toast.type === 'error',
                'bg-amber-600/95 dark:bg-amber-900/95 border-amber-400/50 text-white shadow-amber-950/20': toast.type === 'warning',
                'bg-blue-600/95 dark:bg-blue-900/95 border-blue-400/50 text-white shadow-blue-950/20': toast.type === 'info'
            }"
        >
            <!-- Icon Indicator -->
            <div class="flex-shrink-0 mt-0.5">
                <template x-if="toast.type === 'success'">
                    <div class="w-7 h-7 rounded-xl bg-white/20 flex items-center justify-center font-bold text-base text-white">
                        <i class="ph-bold ph-check-circle"></i>
                    </div>
                </template>
                <template x-if="toast.type === 'danger' || toast.type === 'error'">
                    <div class="w-7 h-7 rounded-xl bg-white/20 flex items-center justify-center font-bold text-base text-white">
                        <i class="ph-bold ph-warning-circle"></i>
                    </div>
                </template>
                <template x-if="toast.type === 'warning'">
                    <div class="w-7 h-7 rounded-xl bg-white/20 flex items-center justify-center font-bold text-base text-white">
                        <i class="ph-bold ph-warning"></i>
                    </div>
                </template>
                <template x-if="toast.type === 'info'">
                    <div class="w-7 h-7 rounded-xl bg-white/20 flex items-center justify-center font-bold text-base text-white">
                        <i class="ph-bold ph-info"></i>
                    </div>
                </template>
            </div>

            <!-- Content -->
            <div class="flex-1 min-w-0 pr-1">
                <h4 class="font-bold text-sm leading-tight text-white" x-text="toast.title"></h4>
                <p class="text-xs text-white/90 mt-1 leading-relaxed" x-show="toast.body" x-text="toast.body"></p>
            </div>

            <!-- Close Button -->
            <button
                @click="removeToast(toast.id)"
                type="button"
                class="flex-shrink-0 text-white/70 hover:text-white transition-colors p-1 rounded-lg hover:bg-white/10"
            >
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>
    </template>
</div>

<script>
    function posToastContainer() {
        return {
            toasts: [],
            initToasts() {
                const handlePayload = (payload) => {
                    if (!payload) return;
                    if (Array.isArray(payload)) {
                        payload.forEach(item => handlePayload(item));
                        return;
                    }
                    const notif = payload.notification || payload.detail || payload;
                    this.addToast({
                        type: notif.type || notif.status || (notif.color === 'danger' ? 'danger' : 'success'),
                        title: notif.title || notif.message || 'Notifikasi',
                        body: notif.body || ''
                    });
                };

                // Check Filament session notifications
                const filamentNotifs = @js(session()->get('filament.notifications', []));
                if (Array.isArray(filamentNotifs)) {
                    filamentNotifs.forEach(n => handlePayload(n));
                }

                // Check standard session flash messages
                @if (session()->has('success'))
                    this.addToast({ type: 'success', title: 'Berhasil', body: @js(session('success')) });
                @endif
                @if (session()->has('error'))
                    this.addToast({ type: 'error', title: 'Gagal', body: @js(session('error')) });
                @endif
                @if (session()->has('warning'))
                    this.addToast({ type: 'warning', title: 'Peringatan', body: @js(session('warning')) });
                @endif
                @if (session()->has('info'))
                    this.addToast({ type: 'info', title: 'Informasi', body: @js(session('info')) });
                @endif

                // 1. Listen to Window DOM events
                window.addEventListener('toast', (e) => handlePayload(e.detail));
                window.addEventListener('notifications.sent', (e) => handlePayload(e.detail));
                window.addEventListener('notificationSent', (e) => handlePayload(e.detail));
                window.addEventListener('filament-notification-sent', (e) => handlePayload(e.detail));

                // 2. Listen to Livewire v3 internal events
                document.addEventListener('livewire:initialized', () => {
                    Livewire.on('toast', (data) => handlePayload(data));
                    Livewire.on('notifications.sent', (data) => handlePayload(data));
                    Livewire.on('notificationSent', (data) => handlePayload(data));
                });
            },
            addToast(detail) {
                if (!detail) return;
                let type = detail.type || detail.status || 'success';
                if (type === 'danger') type = 'error';
                
                const title = detail.title || (type === 'success' ? 'Berhasil' : (type === 'error' ? 'Gagal' : 'Informasi'));
                const body = detail.body || detail.message || (typeof detail === 'string' ? detail : '');

                // Anti Duplicate Check: Ignore identical title & body dispatched within 1000ms
                const now = Date.now();
                const isDuplicate = this.toasts.some(t => t.title === title && t.body === body && (now - t.timestamp) < 1000);
                if (isDuplicate) return;

                const id = now + Math.random();
                const newToast = { id, type, title, body, visible: true, timestamp: now };
                this.toasts.unshift(newToast);

                // Auto remove after 4.5 seconds
                setTimeout(() => {
                    this.removeToast(id);
                }, 4500);
            },
            removeToast(id) {
                const target = this.toasts.find(t => t.id === id);
                if (target) {
                    target.visible = false;
                    setTimeout(() => {
                        this.toasts = this.toasts.filter(t => t.id !== id);
                    }, 300);
                }
            }
        }
    }
</script>
