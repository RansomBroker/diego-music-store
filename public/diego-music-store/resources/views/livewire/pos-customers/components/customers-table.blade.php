<!-- Table Container -->
<x-pos.table.container>
    <x-pos.table>
        <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium">
            <tr>
                <!-- Nama Pelanggan -->
                <x-pos.table.th sortable field="name" :sortField="$sortField" :sortDirection="$sortDirection">
                    Nama Pelanggan
                </x-pos.table.th>
                <!-- Telepon -->
                <x-pos.table.th>
                    Telepon
                </x-pos.table.th>
                <!-- Email -->
                <x-pos.table.th>
                    Email
                </x-pos.table.th>
                <!-- Label -->
                <x-pos.table.th>
                    Label
                </x-pos.table.th>
                <!-- Poin -->
                <x-pos.table.th sortable field="loyalty_points" :sortField="$sortField" :sortDirection="$sortDirection">
                    Poin
                </x-pos.table.th>
                <!-- Member -->
                <x-pos.table.th>
                    Member
                </x-pos.table.th>
                <!-- Piutang -->
                <x-pos.table.th>
                    Sisa Piutang
                </x-pos.table.th>
                <!-- Actions -->
                <x-pos.table.th class="text-right">
                    Aksi
                </x-pos.table.th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-200 dark:divide-slate-800">
            @forelse ($customers as $customer)
                <x-pos.table.tr>
                    <!-- Nama & Alamat -->
                    <x-pos.table.td class="whitespace-nowrap">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-primary/10 dark:bg-blue-950/40 text-primary dark:text-blue-400 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                {{ strtoupper(substr($customer->name, 0, 2)) }}
                            </div>
                            <div>
                                <div class="font-medium text-slate-900 dark:text-slate-100 text-sm">{{ $customer->name }}</div>
                                @if ($customer->address)
                                    <div class="text-xs text-slate-400 dark:text-slate-555 mt-0.5 max-w-[200px] truncate">{{ $customer->address }}</div>
                                @endif
                            </div>
                        </div>
                    </x-pos.table.td>
                    <!-- Telepon -->
                    <x-pos.table.td class="whitespace-nowrap">
                        {{ $customer->phone ?? '—' }}
                    </x-pos.table.td>
                    <!-- Email -->
                    <x-pos.table.td class="whitespace-nowrap">
                        {{ $customer->email ?? '—' }}
                    </x-pos.table.td>
                    <!-- Label badge -->
                    <x-pos.table.td class="whitespace-nowrap">
                        @if ($customer->label)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-blue-50 dark:bg-blue-950/40 text-blue-755 dark:text-blue-400 border border-blue-100 dark:border-blue-900/40">
                                {{ $customer->label->name }}
                            </span>
                        @else
                            <span class="text-slate-350 dark:text-slate-600 text-xs">—</span>
                        @endif
                    </x-pos.table.td>
                    <!-- Poin -->
                    <x-pos.table.td class="whitespace-nowrap font-semibold text-slate-900 dark:text-slate-200">
                        {{ number_format($customer->loyalty_points) }}
                    </x-pos.table.td>
                    <!-- Member Badge -->
                    <x-pos.table.td class="whitespace-nowrap">
                        @if ($customer->is_loyalty_member)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-950/40 text-emerald-755 dark:text-emerald-400 border border-emerald-100 dark:border-emerald-900/40">
                                <i class="ph-fill ph-star text-[10px]"></i> Member
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-slate-100 dark:bg-slate-800 text-slate-650 dark:text-slate-400 border border-slate-200 dark:border-slate-700/60">
                                Umum
                            </span>
                        @endif
                    </x-pos.table.td>
                    <!-- Sisa Piutang -->
                    <x-pos.table.td class="whitespace-nowrap">
                        @if ($customer->total_piutang > 0)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-400 border border-rose-200 dark:border-rose-900/40">
                                Rp {{ number_format($customer->total_piutang, 0, ',', '.') }}
                            </span>
                        @else
                            <span class="text-xs text-slate-400 dark:text-slate-500">Lunas</span>
                        @endif
                    </x-pos.table.td>
                    <!-- Aksi Buttons -->
                    <x-pos.table.td class="whitespace-nowrap text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button
                                type="button"
                                wire:click="openCustomerMessageModal({{ $customer->id }})"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold uppercase text-white bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] transition shadow-sm cursor-pointer"
                                title="Kirim Pesan WhatsApp ke {{ $customer->name }}"
                            >
                                <i class="ph-bold ph-whatsapp-logo text-xs"></i>
                                Pesan WA
                            </button>
                            @if ($customer->total_piutang > 0)
                                <button
                                    type="button"
                                    wire:click="openBillingModal({{ $customer->id }})"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-xs font-bold uppercase text-white bg-emerald-600 hover:bg-emerald-700 active:scale-[0.98] transition shadow-sm cursor-pointer"
                                    title="Kirim Tagihan via WhatsApp"
                                >
                                    <i class="ph-bold ph-receipt text-xs"></i>
                                    Tagihan WA
                                </button>
                            @endif
                            <x-pos.utility.button
                                type="button"
                                variant="warning"
                                size="xs"
                                icon="ph-pencil-simple"
                                wire:click="openEdit({{ $customer->id }})"
                                title="Ubah Pelanggan"
                            >
                                Ubah
                            </x-pos.utility.button>
                            <x-pos.utility.button
                                type="button"
                                variant="danger"
                                size="xs"
                                icon="ph-trash"
                                wire:click="confirmDelete({{ $customer->id }})"
                                title="Hapus Pelanggan"
                            >
                                Hapus
                            </x-pos.utility.button>
                        </div>
                    </x-pos.table.td>
                </x-pos.table.tr>
            @empty
                <x-pos.table.empty colspan="8" icon="ph-users" message="Tidak ada data pelanggan ditemukan" />
            @endforelse
        </tbody>
    </x-pos.table>
    <x-pos.table.footer :paginator="$customers" perPageModel="perPage" />
</x-pos.table.container>
