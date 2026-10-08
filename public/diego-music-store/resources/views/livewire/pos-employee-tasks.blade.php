<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 min-w-0 flex flex-col h-full overflow-hidden">
        <!-- Toast Notification Listener -->
        <x-pos.toast />

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Tugas Karyawan"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">
                
                <!-- Page Header (Title & Breadcrumbs) & Actions -->
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <!-- Breadcrumbs -->
                        <nav class="text-xs font-semibold text-slate-400 dark:text-slate-500 mb-1.5" aria-label="Breadcrumb">
                            <ol class="inline-flex items-center space-x-1 md:space-x-2">
                                <li class="inline-flex items-center">
                                    <a href="/pos/front-office" class="hover:text-primary dark:hover:text-blue-400 transition-colors">POS</a>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                        <span class="text-slate-650 dark:text-slate-300 font-bold">Tugas Karyawan</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <!-- Page Title -->
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Manajemen Tugas Karyawan</h1>
                    </div>
                    
                    <button wire:click="openModal" class="inline-flex items-center justify-center gap-2 bg-primary hover:bg-primary-600 text-white px-5 py-2.5 rounded-xl font-bold transition-all shadow-sm hover:shadow-md hover:shadow-primary/20 focus:ring-4 focus:ring-primary/20">
                        <i class="ph-bold ph-plus text-lg"></i>
                        Buat Tugas Baru
                    </button>
                </div>

                <!-- Table Card Wrapper (Filament Style) -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">
                    
                    <!-- Toolbar (Filters & Search) -->
                    <div class="p-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row gap-4">
                        <div class="flex-1 relative">
                            <i class="ph-bold ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari judul tugas atau nama karyawan..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:border-primary transition-all dark:text-white placeholder-slate-400">
                        </div>
                        <div class="sm:w-48">
                            <select wire:model.live="statusFilter" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-900/50 border border-slate-200 dark:border-slate-700 rounded-xl text-sm font-medium focus:ring-2 focus:ring-primary focus:border-primary transition-all dark:text-white">
                                <option value="">Semua Status</option>
                                <option value="pending">Pending</option>
                                <option value="completed">Selesai (Menunggu Approve)</option>
                                <option value="approved">Disetujui</option>
                                <option value="rejected">Ditolak</option>
                            </select>
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-slate-600 dark:text-slate-300">
                            <thead class="bg-slate-50/50 dark:bg-slate-800/50 text-slate-500 dark:text-slate-400 font-semibold border-b border-slate-200 dark:border-slate-700">
                                <tr>
                                    <th class="px-6 py-4 whitespace-nowrap">Tugas</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Ditugaskan Kepada</th>
                                    <th class="px-6 py-4 whitespace-nowrap">Status</th>
                                    <th class="px-6 py-4 whitespace-nowrap text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 dark:divide-slate-700/50">
                                @forelse($tasks as $task)
                                    <tr class="hover:bg-slate-50/50 dark:hover:bg-slate-800/50 transition-colors group">
                                        <td class="px-6 py-4">
                                            <div class="font-bold text-slate-900 dark:text-white">{{ $task->title }}</div>
                                            <div class="text-xs text-slate-500 mt-1 max-w-md truncate">{{ $task->description ?? '-' }}</div>
                                            <div class="text-[10px] text-slate-400 mt-1">{{ $task->created_at->format('d M Y H:i') }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="font-semibold text-slate-700 dark:text-slate-300">{{ $task->employee->name ?? 'Unknown' }}</div>
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($task->status === 'pending')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300">Pending</span>
                                            @elseif($task->status === 'completed')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400">Menunggu Approve</span>
                                            @elseif($task->status === 'approved')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400">Disetujui</span>
                                            @elseif($task->status === 'rejected')
                                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400">Ditolak</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-right">
                                            <div class="flex items-center justify-end gap-2">
                                                @if($task->status === 'pending' || $task->status === 'rejected')
                                                    <button wire:click="markAsDone({{ $task->id }})" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Tandai Selesai">
                                                        <i class="ph-bold ph-check-circle text-lg"></i>
                                                    </button>
                                                @endif
                                                @if($task->status === 'completed')
                                                    <button wire:click="approveTask({{ $task->id }})" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors" title="Setujui">
                                                        <i class="ph-bold ph-check text-lg"></i>
                                                    </button>
                                                    <button wire:click="rejectTask({{ $task->id }})" class="p-2 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Tolak">
                                                        <i class="ph-bold ph-x text-lg"></i>
                                                    </button>
                                                @endif
                                                
                                                <button wire:click="editTask({{ $task->id }})" class="p-2 text-slate-400 hover:text-primary hover:bg-primary-50 rounded-lg transition-colors" title="Edit Tugas">
                                                    <i class="ph-bold ph-pencil-simple text-lg"></i>
                                                </button>

                                                <button wire:click="confirmDelete({{ $task->id }})" class="p-2 text-slate-400 hover:text-rose-600 hover:bg-rose-50 rounded-lg transition-colors" title="Hapus Tugas">
                                                    <i class="ph-bold ph-trash text-lg"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-12 text-center text-slate-500">
                                            <div class="flex flex-col items-center justify-center">
                                                <i class="ph-duotone ph-clipboard-text text-4xl mb-3 text-slate-300 dark:text-slate-600"></i>
                                                <p class="font-medium">Belum ada data tugas.</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($tasks->hasPages())
                        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">
                            {{ $tasks->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Form -->
    <x-pos.modal wire:model="showModal" title="{{ $isEdit ? 'Edit Tugas' : 'Buat Tugas Baru' }}">
        <form wire:submit.prevent="saveTask" class="space-y-4">
            <div>
                <x-pos.form.input 
                    label="Judul Tugas" 
                    model="title" 
                    placeholder="Contoh: Rapikan display gitar" 
                    required="true"
                    rounded="rounded-xl"
                />
            </div>
            
            <div>
                <x-pos.form.textarea 
                    label="Deskripsi" 
                    model="description" 
                    placeholder="Detail instruksi..." 
                    rounded="rounded-xl"
                    rows="3"
                />
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-300 mb-1.5">Tugaskan Kepada <span class="text-rose-500">*</span></label>
                <x-pos.form.multiselect 
                    model="employee_ids" 
                    :options="$employees"
                    rounded="rounded-xl" 
                    icon="ph-users" 
                    placeholder="Pilih Karyawan"
                />
                @error('employee_ids') <span class="text-xs text-rose-500 mt-1">{{ $message }}</span> @enderror
            </div>

            <div class="pt-4 border-t border-slate-200 dark:border-slate-700 flex justify-end gap-3">
                <button type="button" wire:click="closeModal" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Batal
                </button>
                <button type="submit" class="px-5 py-2.5 rounded-xl font-bold bg-primary text-white hover:bg-primary-600 transition-colors shadow-sm">
                    {{ $isEdit ? 'Simpan Perubahan' : 'Kirim Tugas' }}
                </button>
            </div>
        </form>
    </x-pos.modal>

    <!-- Modal Konfirmasi Hapus -->
    <x-pos.modal wire:model="showDeleteModal" title="Konfirmasi Hapus" maxWidth="md">
        <div class="text-center p-4">
            <div class="w-16 h-16 rounded-full bg-rose-100 dark:bg-rose-900/30 text-rose-500 flex items-center justify-center mx-auto mb-4">
                <i class="ph-bold ph-trash text-3xl"></i>
            </div>
            <h3 class="text-lg font-black text-slate-800 dark:text-slate-100 mb-2">Hapus Tugas Ini?</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">
                Apakah Anda yakin ingin menghapus tugas ini? Tindakan ini tidak dapat dibatalkan.
            </p>
            <div class="flex items-center justify-center gap-3">
                <button wire:click="$set('showDeleteModal', false)" type="button" class="px-5 py-2.5 rounded-xl font-bold text-slate-600 dark:text-slate-300 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">
                    Batal
                </button>
                <button wire:click="destroyTask" type="button" class="px-5 py-2.5 rounded-xl font-bold bg-rose-500 text-white hover:bg-rose-600 transition-colors shadow-sm">
                    Ya, Hapus
                </button>
            </div>
        </div>
    </x-pos.modal>
</div>
