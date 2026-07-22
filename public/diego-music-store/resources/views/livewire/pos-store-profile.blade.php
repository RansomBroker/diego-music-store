<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Register Nama Toko & Profil Store"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header (Title & Breadcrumbs) -->
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
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
                                        <span class="text-slate-400 dark:text-slate-500">Utility</span>
                                    </div>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                        <span class="text-slate-650 dark:text-slate-300 font-bold">Register Nama Toko</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Register & Profil Toko</h1>
                    </div>

                    <button
                        wire:click="openCreateStore"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer self-start sm:self-auto"
                    >
                        <i class="ph-bold ph-plus text-sm"></i>
                        <span>Register Toko Baru</span>
                    </button>
                </div>

                <!-- Main Layout Grid (Branch Selector & Form & Card Preview) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

                    <!-- Left Column: Form Edit Profil Toko -->
                    <div class="lg:col-span-2 space-y-6">
                        <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl p-6 transition-colors duration-200">
                            
                            <!-- Branch Selector Tabs -->
                            <div class="mb-6 pb-4 border-b border-slate-100 dark:border-slate-800">
                                <label class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Pilih Toko / Cabang</label>
                                <div class="flex flex-wrap gap-2">
                                    @foreach ($branches as $b)
                                        <button
                                            type="button"
                                            wire:click="selectBranch({{ $b->id }})"
                                            class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all cursor-pointer border {{ $selectedBranchId === $b->id ? 'bg-primary text-white border-primary shadow-sm' : 'bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 border-slate-200 dark:border-slate-700 hover:bg-slate-200' }}"
                                        >
                                            <i class="ph-bold ph-storefront mr-1"></i>
                                            {{ $b->store_name ?: $b->name }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>

                            <form wire:submit.prevent="save" class="space-y-5">
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Nama Toko (Brand Name) -->
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Toko (Brand) <span class="text-rose-500">*</span></label>
                                        <input
                                            type="text"
                                            wire:model="store_name"
                                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                                            placeholder="e.g. Diego Music Store"
                                        >
                                        @error('store_name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Nama Cabang -->
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Cabang <span class="text-rose-500">*</span></label>
                                        <input
                                            type="text"
                                            wire:model="name"
                                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                                            placeholder="e.g. Cabang Utama Jakarta"
                                        >
                                        @error('name') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- No. Telepon / WhatsApp -->
                                    <div>
                                        <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Telepon / WhatsApp</label>
                                        <input
                                            type="text"
                                            wire:model="phone"
                                            class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                                            placeholder="e.g. 0812-3456-7890"
                                        >
                                        @error('phone') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                    </div>

                                    <!-- Status Aktif -->
                                    <div class="flex items-center justify-between p-2.5 border border-slate-200 dark:border-slate-700 rounded-lg bg-slate-50/50 dark:bg-slate-950/40">
                                        <div>
                                            <span class="block text-xs font-bold text-slate-700 dark:text-slate-300">Status Operasional</span>
                                            <span class="block text-[10px] text-slate-400">Aktif untuk transaksi POS</span>
                                        </div>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" wire:model="is_active" class="sr-only peer">
                                            <div class="w-9 h-5 bg-slate-200 dark:bg-slate-700 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-slate-300 after:border after:rounded-full after:h-4 after:w-4 after:transition-all dark:border-slate-600 peer-checked:bg-primary"></div>
                                        </label>
                                    </div>
                                </div>

                                <!-- Alamat Lengkap -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap Toko</label>
                                    <textarea
                                        wire:model="address"
                                        rows="3"
                                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white focus:border-primary focus:ring-1 focus:ring-primary outline-none transition-colors"
                                        placeholder="Alamat jalan, gedung, RT/RW, kota..."
                                    ></textarea>
                                    @error('address') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                </div>

                                <!-- Upload Logo -->
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Logo Toko</label>
                                    <div class="flex items-center gap-4">
                                        <div class="w-16 h-16 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden bg-slate-100 dark:bg-slate-800 flex items-center justify-center flex-shrink-0">
                                            @if ($logo)
                                                <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                                            @elseif ($currentLogoUrl)
                                                <img src="{{ $currentLogoUrl }}" class="w-full h-full object-cover">
                                            @else
                                                <i class="ph-bold ph-storefront text-2xl text-slate-400"></i>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <input
                                                type="file"
                                                wire:model="logo"
                                                accept="image/*"
                                                class="block w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-light file:text-primary hover:file:bg-primary/20 dark:file:bg-slate-800 dark:file:text-blue-400 cursor-pointer"
                                            >
                                            <span class="text-[10px] text-slate-400 mt-1 block">Format PNG, JPG, WebP. Maksimal 2MB.</span>
                                            @error('logo') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="pt-4 border-t border-slate-200 dark:border-slate-800 flex justify-end">
                                    <button
                                        type="submit"
                                        class="inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-lg shadow-sm hover:shadow transition duration-150 cursor-pointer"
                                    >
                                        <i class="ph-bold ph-floppy-disk text-base"></i>
                                        <span>Simpan Profil Toko</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Live Store Badge Card Preview -->
                    <div class="space-y-6">
                        <div class="bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-2xl p-6 shadow-xl border border-slate-700/50 space-y-4 relative overflow-hidden">
                            <div class="absolute -right-8 -top-8 w-32 h-32 bg-primary/20 rounded-full blur-2xl pointer-events-none"></div>

                            <div class="flex items-center gap-4">
                                <div class="w-14 h-14 rounded-2xl overflow-hidden bg-white/10 backdrop-blur border border-white/20 flex items-center justify-center flex-shrink-0 shadow-lg">
                                    @if ($logo)
                                        <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                                    @elseif ($currentLogoUrl)
                                        <img src="{{ $currentLogoUrl }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="ph-bold ph-storefront text-3xl text-primary-light"></i>
                                    @endif
                                </div>
                                <div>
                                    <span class="text-[10px] font-black tracking-widest text-primary-light uppercase">Official Store Badge</span>
                                    <h3 class="text-lg font-black leading-tight">{{ $store_name ?: 'Nama Toko' }}</h3>
                                    <p class="text-xs text-slate-300 font-medium">{{ $name ?: 'Nama Cabang' }}</p>
                                </div>
                            </div>

                            <div class="border-t border-white/10 pt-3 space-y-2 text-xs text-slate-300">
                                <div class="flex items-start gap-2">
                                    <i class="ph-fill ph-map-pin text-primary-light text-sm mt-0.5"></i>
                                    <span>{{ $address ?: 'Alamat belum diatur' }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <i class="ph-fill ph-phone text-primary-light text-sm"></i>
                                    <span>{{ $phone ?: 'No. Telp belum diatur' }}</span>
                                </div>
                            </div>

                            <div class="pt-2 flex items-center justify-between text-[11px] font-semibold text-slate-400 border-t border-white/10">
                                <span>Status POS</span>
                                @if ($is_active)
                                    <span class="px-2.5 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">OPERASIONAL</span>
                                @else
                                    <span class="px-2.5 py-0.5 rounded-full bg-rose-500/20 text-rose-300 border border-rose-500/30">NON-AKTIF</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Create Store / Cabang -->
    <x-pos.modal
        wire:model="showCreateModal"
        title="Register Toko / Cabang Baru"
        subtitle="Daftarkan cabang atau outlet toko baru ke sistem ERP"
        icon="ph-storefront"
        maxWidth="lg"
    >
        <form wire:submit.prevent="createStore" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Brand Toko <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    wire:model="store_name"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                    placeholder="e.g. Diego Music Store"
                >
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Nama Cabang <span class="text-rose-500">*</span></label>
                <input
                    type="text"
                    wire:model="name"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                    placeholder="e.g. Cabang Bandung"
                >
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">No. Telepon / WA</label>
                <input
                    type="text"
                    wire:model="phone"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                    placeholder="e.g. 0822-1111-2222"
                >
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">Alamat Lengkap</label>
                <textarea
                    wire:model="address"
                    rows="2"
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-white"
                ></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button
                    type="button"
                    wire:click="$set('showCreateModal', false)"
                    class="px-4 py-2 border border-slate-300 dark:border-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-bold rounded-lg"
                >
                    Register Toko
                </button>
            </div>
        </form>
    </x-pos.modal>
</div>
