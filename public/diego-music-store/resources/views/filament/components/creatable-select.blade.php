<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div x-data="{
        open: false,
        search: '',
        value: @entangle($getStatePath()),
        options: {{ json_encode($getOptions()) }},
        get filteredOptions() {
            if (!this.search) return this.options;
            return Object.fromEntries(
                Object.entries(this.options).filter(([key, val]) => 
                    val.toLowerCase().includes(this.search.toLowerCase())
                )
            );
        },
        selectOption(key) {
            this.value = key;
            this.search = '';
            this.open = false;
        },
        createOption() {
            if (!this.search) return;
            const newOpt = this.search.trim();
            this.options[newOpt] = newOpt;
            this.value = newOpt;
            this.search = '';
            this.open = false;
        }
    }"
    class="relative w-full"
    style="position: relative;"
>
        <!-- Trigger/Button Wrapper with explicit matching border -->
        <div 
            class="flex rounded-lg shadow-sm transition duration-75 focus-within:ring-2 focus-within:ring-primary-600 dark:focus-within:ring-primary-500"
            x-bind:style="'border: 1px solid ' + (document.documentElement.classList.contains('dark') ? '#374151' : '#d1d5db') + '; background-color: ' + (document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.05)' : '#ffffff') + ';'"
        >
            <button
                type="button"
                @click="open = !open; if(open) { $nextTick(() => $refs.searchInput.focus()) }"
                class="flex h-9 w-full items-center justify-between px-3 py-2 text-sm text-left bg-transparent border-none outline-none"
                style="border: none; background: transparent; width: 100%; text-align: left; display: flex; align-items: center; justify-content: space-between;"
            >
                <span 
                    x-text="options[value] || value || 'Pilih klasifikasi...'" 
                    x-bind:style="value ? ('color: ' + (document.documentElement.classList.contains('dark') ? '#ffffff' : '#111827') + ';') : ('color: ' + (document.documentElement.classList.contains('dark') ? '#9ca3af' : '#9ca3af') + ';')"
                    class="truncate"
                ></span>
                <!-- Chevron Icon -->
                <svg class="h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" style="width: 20px; height: 20px;">
                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>

        <!-- Dropdown Menu -->
        <div
            x-show="open"
            @click.away="open = false"
            x-transition
            class="absolute z-50 mt-1 w-full rounded-lg p-2 shadow-lg"
            :style="open ? 'display: block; position: absolute; width: 100%; z-index: 9999; margin-top: 4px; border-radius: 8px; padding: 8px; border: 1px solid ' + (document.documentElement.classList.contains('dark') ? '#374151' : '#d1d5db') + '; background-color: ' + (document.documentElement.classList.contains('dark') ? '#1f2937' : '#ffffff') + ';' : 'display: none;'"
        >
            <!-- Search Input Wrapper with explicit matching border -->
            <div 
                class="flex rounded-lg shadow-sm transition duration-75 mb-2 focus-within:ring-2 focus-within:ring-primary-600 dark:focus-within:ring-primary-500"
                x-bind:style="'border: 1px solid ' + (document.documentElement.classList.contains('dark') ? '#374151' : '#d1d5db') + '; background-color: ' + (document.documentElement.classList.contains('dark') ? 'rgba(255,255,255,0.05)' : '#f9fafb') + ';'"
            >
                <input
                    x-ref="searchInput"
                    x-model="search"
                    type="text"
                    placeholder="Cari atau ketik klasifikasi baru..."
                    @keydown.enter.prevent="
                        const opts = Object.keys(filteredOptions);
                        if (opts.length === 1) {
                            selectOption(opts[0]);
                        } else if (opts.length === 0) {
                            createOption();
                        }
                    "
                    class="h-8 w-full bg-transparent px-3 text-sm focus:outline-none focus:ring-0 border-none outline-none"
                    x-bind:style="'color: ' + (document.documentElement.classList.contains('dark') ? '#ffffff' : '#111827') + '; border: none; background: transparent; width: 100%; height: 32px; padding: 0 12px; outline: none;'"
                />
            </div>

            <!-- Options Container -->
            <div class="max-h-60 overflow-y-auto flex flex-col gap-1" style="max-height: 240px; overflow-y: auto; display: flex; flex-direction: column; gap: 4px;">
                <template x-for="(label, key) in filteredOptions" :key="key">
                    <button
                        type="button"
                        @click="selectOption(key)"
                        :class="value === key ? 'bg-primary-50 dark:bg-primary-500/10 font-medium' : 'hover:bg-gray-50 dark:hover:bg-white/5'"
                        x-bind:style="value === key ? 'color: #2563eb;' : 'color: ' + (document.documentElement.classList.contains('dark') ? '#e5e7eb' : '#1f2937') + ';'"
                        class="flex w-full items-center rounded-md px-3 py-1.5 text-left text-sm border-none"
                        style="width: 100%; border: none; text-align: left; padding: 6px 12px; border-radius: 6px; font-size: 14px;"
                    >
                        <span x-text="label"></span>
                    </button>
                </template>

                <!-- Dynamic Option Addition -->
                <div x-show="Object.keys(filteredOptions).length === 0 && search" class="pt-1" style="padding-top: 4px;">
                    <button
                        type="button"
                        @click="createOption()"
                        class="flex w-full items-center rounded-md bg-primary-50 hover:bg-primary-100 dark:bg-primary-950/40 dark:hover:bg-primary-950/60 px-3 py-1.5 text-left text-sm text-primary-600 dark:text-primary-400 font-medium border-none"
                        style="width: 100%; border: none; text-align: left; padding: 6px 12px; border-radius: 6px; font-size: 14px;"
                    >
                        <span x-text="`+ Tambah '${search}'`"></span>
                    </button>
                </div>

                <!-- Empty State -->
                <div x-show="Object.keys(filteredOptions).length === 0 && !search" class="px-3 py-2 text-sm text-center" x-bind:style="'color: ' + (document.documentElement.classList.contains('dark') ? '#9ca3af' : '#6b7280') + ';'" style="padding: 8px 12px; text-align: center; font-size: 14px;">
                    Tidak ada opsi
                </div>
            </div>
        </div>
    </div>
</x-dynamic-component>
