<div
    x-data="{
        isOpen: false,
        title: '',
        message: '',
        confirmAction: null,
        confirmLabel: 'Ya, Lanjutkan',
        cancelLabel: 'Batal',
        isDanger: false,
        
        open(detail) {
            this.title = detail.title || 'Konfirmasi';
            this.message = detail.message || 'Apakah Anda yakin?';
            this.confirmAction = detail.onConfirm || null;
            this.confirmLabel = detail.confirmLabel || 'Ya, Lanjutkan';
            this.cancelLabel = detail.cancelLabel || 'Batal';
            this.isDanger = detail.isDanger || false;
            this.isOpen = true;
        },
        confirm() {
            if (this.confirmAction) {
                if (typeof this.confirmAction === 'string') {
                    if (this.confirmAction.startsWith('redirect:')) {
                        window.location.href = this.confirmAction.replace('redirect:', '');
                    } else if (this.confirmAction.startsWith('livewire:')) {
                        const method = this.confirmAction.replace('livewire:', '');
                        $wire.call(method);
                    } else {
                        // General JS evaluation
                        new Function(this.confirmAction)();
                    }
                } else if (typeof this.confirmAction === 'function') {
                    this.confirmAction();
                }
            }
            this.isOpen = false;
        }
    }"
    @confirm-open.window="open($event.detail)"
    @keydown.window.escape="isOpen = false"
    x-show="isOpen"
    x-cloak
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    x-transition:enter="transition ease-out duration-200"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-150"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <!-- Modal Card -->
    <div 
        x-show="isOpen"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 scale-95 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 translate-y-4"
        @click.away="isOpen = false"
        class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-3xl p-6 shadow-2xl max-w-sm w-full relative overflow-hidden"
    >
        <!-- Top Visual Indicator / Icon -->
        <div class="flex items-center gap-4">
            <div 
                :class="isDanger ? 'bg-red-50 dark:bg-red-950/30 text-red-600 dark:text-red-400' : 'bg-amber-50 dark:bg-amber-950/30 text-amber-600 dark:text-amber-400'"
                class="w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0"
            >
                <i :class="isDanger ? 'ph-bold ph-warning-octagon text-2xl' : 'ph-bold ph-warning-circle text-2xl'"></i>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-black text-slate-900 dark:text-white leading-tight" x-text="title"></h3>
                <p class="text-xs font-semibold text-slate-500 dark:text-slate-400 mt-1" x-text="message"></p>
            </div>
        </div>

        <!-- Buttons Footer -->
        <div class="flex items-center justify-end gap-3 mt-6">
            <x-pos.utility.button 
                @click="isOpen = false"
                variant="secondary"
                size="sm"
            >
                <span x-text="cancelLabel"></span>
            </x-pos.utility.button>
            <x-pos.utility.button 
                @click="confirm()"
                variant="primary"
                size="sm"
                ::class="isDanger ? '!bg-red-600 hover:!bg-red-700 !text-white' : '!bg-amber-500 hover:!bg-amber-600 !text-white'"
            >
                <span x-text="confirmLabel"></span>
            </x-pos.utility.button>
        </div>
    </div>
</div>
