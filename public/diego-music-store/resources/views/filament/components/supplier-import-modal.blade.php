<div x-data="{ tab: 'debt' }" class="space-y-4">
    <!-- Tab Selector -->
    <div class="flex items-center gap-2 p-1 bg-gray-100 dark:bg-gray-800 rounded-xl max-w-md">
        <button type="button" 
                @click="tab = 'debt'" 
                :class="tab === 'debt' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 font-medium'"
                class="flex-1 py-1.5 px-3 text-xs rounded-lg transition duration-150 flex items-center justify-center gap-1.5 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Saldo Awal Faktur Hutang
        </button>
        <button type="button" 
                @click="tab = 'master'" 
                :class="tab === 'master' ? 'bg-white dark:bg-gray-700 text-blue-600 dark:text-blue-400 shadow-xs font-bold' : 'text-gray-600 dark:text-gray-400 hover:text-gray-900 font-medium'"
                class="flex-1 py-1.5 px-3 text-xs rounded-lg transition duration-150 flex items-center justify-center gap-1.5 cursor-pointer">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            Data Master Supplier
        </button>
    </div>

    <div x-show="tab === 'debt'">
        @livewire('backoffice.spreadsheet-importer', ['type' => 'supplier_debt'], key('supplier-debt-importer-' . now()->timestamp))
    </div>

    <div x-show="tab === 'master'" x-cloak>
        @livewire('backoffice.spreadsheet-importer', ['type' => 'supplier'], key('supplier-master-importer-' . now()->timestamp))
    </div>
</div>
