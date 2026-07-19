{{--
    Komponen Modal POS Reusable — <x-pos.modal wire:model="showModal" ...>
    ===================================================================
    Props:
      - title    : string  — judul modal (wajib)
      - subtitle : string  — subjudul modal (opsional)
      - icon     : string  — class icon Phosphor (opsional)
      - maxWidth : string  — ukuran modal: 'sm', 'md', 'lg', 'xl', '2xl', '3xl', '4xl', '5xl' (default: 2xl)
--}}
@props([
    'title'    => '',
    'subtitle' => '',
    'icon'     => '',
    'maxWidth' => '2xl',
])

@php
    $maxWidthClass = [
        'sm'  => 'max-w-sm',
        'md'  => 'max-w-md',
        'lg'  => 'max-w-lg',
        'xl'  => 'max-w-xl',
        '2xl' => 'max-w-2xl',
        '3xl' => 'max-w-3xl',
        '4xl' => 'max-w-4xl',
        '5xl' => 'max-w-5xl',
    ][$maxWidth] ?? 'max-w-2xl';
@endphp

<div
    x-data="{ show: @entangle($attributes->wire('model')) }"
    x-show="show"
    x-on:keydown.escape.window="show = false"
    class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm"
    style="display: none;"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
>
    <!-- Modal Container -->
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        @click.outside="show = false"
        class="bg-white dark:bg-slate-800 rounded-3xl border border-slate-200 dark:border-slate-700 w-full {{ $maxWidthClass }} shadow-2xl shadow-slate-900/20 transition-colors overflow-hidden flex flex-col max-h-[90vh]"
    >
        <!-- Modal Header -->
        <div class="flex items-center justify-between px-7 py-5 border-b border-slate-100 dark:border-slate-700 flex-shrink-0">
            <div class="flex items-center gap-3">
                @if ($icon)
                    <div class="w-9 h-9 rounded-xl bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center">
                        <i class="ph-bold {{ $icon }} text-base"></i>
                    </div>
                @endif
                <div>
                    <h3 class="text-base font-black text-slate-800 dark:text-slate-100">
                        {{ $title }}
                    </h3>
                    @if ($subtitle)
                        <p class="text-xs text-slate-400 dark:text-slate-500 mt-0.5">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            <button @click="show = false" type="button" class="w-8 h-8 rounded-xl bg-slate-100 dark:bg-slate-700 hover:bg-slate-200 dark:hover:bg-slate-600 text-slate-500 dark:text-slate-400 flex items-center justify-center transition-colors cursor-pointer">
                <i class="ph-bold ph-x text-sm"></i>
            </button>
        </div>

        <!-- Modal Body (Scrollable if content is long) -->
        <div class="p-7 overflow-y-auto no-scrollbar flex-1">
            {{ $slot }}
        </div>
    </div>
</div>
