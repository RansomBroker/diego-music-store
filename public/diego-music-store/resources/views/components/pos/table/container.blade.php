<div {{ $attributes->merge(['class' => 'bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200']) }}>
    <div class="overflow-x-auto relative">
        <!-- Wire Loading Progress Bar -->
        <div wire:loading.block class="absolute top-0 left-0 right-0 h-0.5 bg-primary/20 overflow-hidden">
            <div class="h-full bg-primary animate-pulse w-1/3"></div>
        </div>
        {{ $slot }}
    </div>
</div>
