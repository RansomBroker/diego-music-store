<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Data Voucher Belanja"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header -->
                <div>
                    <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                        <ol class="inline-flex items-center space-x-1 md:space-x-2">
                            <li class="inline-flex items-center">
                                <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                    <span class="text-slate-400 dark:text-slate-500">Input Data</span>
                                </div>
                            </li>
                            <li>
                                <div class="flex items-center">
                                    <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                    <span class="text-slate-655 dark:text-slate-300 font-bold">Data Voucher</span>
                                </div>
                            </li>
                        </ol>
                    </nav>
                    <h1 class="text-2xl font-black text-slate-900 dark:text-white tracking-tight">Management Voucher Belanja</h1>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Kelola voucher promo, nominal diskon, tanggal kadaluarsa, dan kuota penggunaan kasir.</p>
                </div>

                <!-- Table Card Container -->
                <div class="bg-white dark:bg-slate-900 rounded-2xl border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden transition-colors">
                    <!-- Top Action Bar -->
                    <div class="p-5 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <!-- Search Box -->
                        <div class="relative w-full sm:w-80">
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-950 border border-slate-200 dark:border-slate-800 rounded-xl text-sm text-slate-900 dark:text-white placeholder-slate-400 focus:outline-none focus:border-primary dark:focus:border-blue-500 transition-colors"
                                placeholder="Cari kode atau nama voucher..."
                            >
                            <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-base"></i>
                        </div>

                        <!-- Add Button -->
                        <button
                            wire:click="openCreate"
                            class="w-full sm:w-auto px-4 py-2.5 bg-primary hover:bg-primary-dark text-white rounded-xl text-xs font-bold transition-all shadow-md shadow-primary/20 flex items-center justify-center gap-2 cursor-pointer"
                        >
                            <i class="ph-bold ph-plus text-sm"></i>
                            <span>Tambah Voucher</span>
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 dark:bg-slate-950/60 border-b border-slate-200 dark:border-slate-800 text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wider">
                                    <th class="px-6 py-4">Kode Voucher</th>
                                    <th class="px-6 py-4">Nama Voucher</th>
                                    <th class="px-6 py-4">Nilai Diskon</th>
                                    <th class="px-6 py-4">Min. Belanja</th>
                                    <th class="px-6 py-4">Kadaluarsa</th>
                                    <th class="px-6 py-4 text-center">Penggunaan</th>
                                    <th class="px-6 py-4 text-center">Status</th>
                                    <th class="px-6 py-4 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-sm">
                                @forelse ($vouchers as $row)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors">
                                        <td class="px-6 py-4 font-black text-primary dark:text-blue-400">
                                            <span class="px-2.5 py-1 bg-primary/10 dark:bg-blue-950/40 rounded-lg text-xs tracking-wider">
                                                {{ $row->code }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 font-semibold text-slate-900 dark:text-white">
                                            {{ $row->name }}
                                        </td>
                                        <td class="px-6 py-4 font-bold text-emerald-600 dark:text-emerald-400">
                                            @if ($row->type === 'percent')
                                                {{ $row->value }}%
                                            @else
                                                Rp {{ number_format($row->value, 0, ',', '.') }}
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-slate-600 dark:text-slate-300 font-medium">
                                            @if ($row->min_spend > 0)
                                                Rp {{ number_format($row->min_spend, 0, ',', '.') }}
                                            @else
                                                <span class="text-slate-400 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-xs font-semibold text-slate-600 dark:text-slate-300">
                                            @if ($row->valid_until)
                                                {{ $row->valid_until->format('d M Y H:i') }}
                                            @else
                                                <span class="text-slate-400">Selamanya</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center text-xs font-bold">
                                            <span class="text-slate-800 dark:text-slate-200">{{ $row->used_count }}</span>
                                            <span class="text-slate-400">/ {{ $row->max_uses ?? '∞' }}</span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if ($row->is_active)
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-950/30 text-emerald-700 dark:text-emerald-400 border border-emerald-200/50">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/30 text-rose-700 dark:text-rose-400 border border-rose-200/50">
                                                    Non-aktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-3">
                                                <button wire:click="openEdit({{ $row->id }})" class="text-xs font-bold text-primary hover:underline cursor-pointer">
                                                    Ubah
                                                </button>
                                                <span class="text-slate-300">|</span>
                                                <button wire:click="confirmDelete({{ $row->id }})" class="text-xs font-bold text-rose-600 hover:underline cursor-pointer">
                                                    Hapus
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-8 text-slate-400 text-xs font-semibold">
                                            Tidak ada voucher ditemukan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Form Create / Edit -->
    <x-pos.modal
        wire:model="showModal"
        :title="$isEditing ? 'Ubah Voucher' : 'Tambah Voucher Baru'"
        :subtitle="$isEditing ? 'Perbarui detail dan syarat voucher' : 'Buat voucher promo diskon baru'"
        icon="ph-ticket"
        maxWidth="lg"
    >
        <form wire:submit.prevent="save" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Kode Voucher -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Kode Voucher <span class="text-rose-500">*</span></label>
                    <input
                        type="text"
                        wire:model="code"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm uppercase font-bold text-slate-900 dark:text-white"
                        placeholder="e.g. PROMO50K"
                    >
                    @error('code') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Tipe Diskon -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Tipe Diskon <span class="text-rose-500">*</span></label>
                    <select wire:model="type" class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white">
                        <option value="fixed">Nominal Tetap (Rp)</option>
                        <option value="percent">Persentase (%)</option>
                    </select>
                </div>
            </div>

            <!-- Nama Voucher -->
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Nama / Deskripsi Voucher <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    wire:model="name"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                    placeholder="e.g. Voucher Diskon Rp 50.000 Promo Grand Opening"
                >
                @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nilai Diskon -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Nilai Diskon <span class="text-rose-500">*</span></label>
                    <input
                        type="number"
                        wire:model="value"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-900 dark:text-white"
                        placeholder="0"
                    >
                    @error('value') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>

                <!-- Min Spend -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Min. Belanja (Rp)</label>
                    <input
                        type="number"
                        wire:model="min_spend"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm font-bold text-slate-900 dark:text-white"
                        placeholder="0"
                    >
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Valid Until -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Masa Berlaku (Kadaluarsa)</label>
                    <input
                        type="datetime-local"
                        wire:model="valid_until"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-white"
                    >
                </div>

                <!-- Max Uses -->
                <div>
                    <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">Kuota Maksimal Penggunaan</label>
                    <input
                        type="number"
                        wire:model="max_uses"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                        placeholder="Kosongkan jika tak terbatas"
                    >
                </div>
            </div>

            <!-- Status Aktif -->
            <div class="flex items-center gap-2 pt-2">
                <input type="checkbox" wire:model="is_active" id="vch_active" class="rounded border-slate-300 text-primary">
                <label for="vch_active" class="text-xs font-semibold text-slate-600 dark:text-slate-400 cursor-pointer">Voucher Aktif</label>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button type="button" wire:click="$set('showModal', false)" class="px-4 py-2 text-xs font-semibold text-slate-600 border border-slate-300 rounded-lg">
                    Batal
                </button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-primary hover:bg-primary-dark rounded-lg shadow-sm">
                    Simpan Voucher
                </button>
            </div>
        </form>
    </x-pos.modal>
</div>
