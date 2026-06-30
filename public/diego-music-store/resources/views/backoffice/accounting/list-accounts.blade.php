<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Style override to hide table visual components but keep modals active -->
        <style>
            .hide-table-only .fi-ta-header,
            .hide-table-only .fi-ta-ctn,
            .hide-table-only .fi-ta-empty-state,
            .hide-table-only .fi-ta-pagination,
            .hide-table-only .fi-ta-footer {
                display: none !important;
            }
        </style>

        <!-- Tree View Content -->
        @if ($this->isTreeView)
            <div class="space-y-6">
                @if ($this->getHeaderWidgets())
                    <x-filament-widgets::widgets
                        :widgets="$this->getHeaderWidgets()"
                        :columns="$this->getHeaderWidgetsColumns()"
                    />
                @endif

                <div class="p-6 bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                    <div class="flex items-center justify-between mb-6 pb-4 border-b border-gray-100 dark:border-gray-800">
                        <div>
                            <h2 class="text-base font-bold text-gray-900 dark:text-white">Bagan Akun Keuangan (Chart of Accounts Tree)</h2>
                            <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Struktur hierarki folder induk dan rekening transaksi aktif</p>
                        </div>
                    </div>

                    <div class="space-y-2">
                        @php
                            $rootAccounts = \App\Models\Account::whereNull('parent_id')->orderBy('code')->get();
                        @endphp

                        @forelse($rootAccounts as $rootAccount)
                            @include('backoffice.accounting.tree-node', ['account' => $rootAccount, 'depth' => 0])
                        @empty
                            <div class="text-center py-8 text-gray-400 dark:text-gray-500">
                                Belum ada data akun yang dikonfigurasi. Silakan buat akun baru.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endif

        <!-- Table View Content (Always rendered in the DOM, visually hidden if isTreeView is true) -->
        <div class="{{ $this->isTreeView ? 'hide-table-only' : 'space-y-4' }}">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
