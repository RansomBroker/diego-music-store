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

            <!-- Lokasi GPS & Radius Absensi dengan Peta Leaflet.js -->
            <div class="p-4 bg-slate-50 dark:bg-slate-950/60 rounded-xl border border-slate-200 dark:border-slate-800 space-y-3"
                 x-data="{
                    gettingGps: false,
                    map: null,
                    marker: null,
                    circle: null,
                    initMap() {
                        if (!window.L) return;
                        this.$nextTick(() => {
                            const container = this.$refs.storeMapContainer;
                            if (!container) return;
                            if (this.map) this.map.remove();

                            let lat = parseFloat($wire.latitude) || -0.03470087552402962;
                            let lng = parseFloat($wire.longitude) || 109.33239215349418;
                            let radius = parseInt($wire.attendance_radius_meters) || 100;

                            this.map = L.map(container).setView([lat, lng], 16);

                            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '© OpenStreetMap'
                            }).addTo(this.map);

                            this.circle = L.circle([lat, lng], {
                                color: '#3b82f6',
                                fillColor: '#93c5fd',
                                fillOpacity: 0.3,
                                radius: radius
                            }).addTo(this.map);

                            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map)
                                .bindPopup('<b>Lokasi Cabang</b><br>Geser pin untuk ubah koordinat');

                            this.marker.on('dragend', (e) => {
                                const newPos = e.target.getLatLng();
                                $wire.latitude = parseFloat(newPos.lat.toFixed(7));
                                $wire.longitude = parseFloat(newPos.lng.toFixed(7));
                                this.updateCircle();
                            });

                            $wire.$watch('latitude', () => this.updateMapFromWire());
                            $wire.$watch('longitude', () => this.updateMapFromWire());
                            $wire.$watch('attendance_radius_meters', () => this.updateCircle());
                        });
                    },
                    updateMapFromWire() {
                        let lat = parseFloat($wire.latitude) || -0.03470087552402962;
                        let lng = parseFloat($wire.longitude) || 109.33239215349418;
                        if (this.marker && this.map) {
                            this.marker.setLatLng([lat, lng]);
                            this.map.setView([lat, lng], 16);
                            this.updateCircle();
                        }
                    },
                    updateCircle() {
                        let lat = parseFloat($wire.latitude) || -0.03470087552402962;
                        let lng = parseFloat($wire.longitude) || 109.33239215349418;
                        let radius = parseInt($wire.attendance_radius_meters) || 100;
                        if (this.circle) {
                            this.circle.setLatLng([lat, lng]);
                            this.circle.setRadius(radius);
                        }
                    },
                    getGps() {
                        this.gettingGps = true;
                        if ('geolocation' in navigator) {
                            navigator.geolocation.getCurrentPosition((pos) => {
                                $wire.latitude = parseFloat(pos.coords.latitude.toFixed(7));
                                $wire.longitude = parseFloat(pos.coords.longitude.toFixed(7));
                                this.gettingGps = false;
                                this.updateMapFromWire();
                            }, (err) => {
                                this.gettingGps = false;
                                alert('Gagal mendeteksi lokasi GPS: ' + err.message);
                            }, { enableHighAccuracy: true });
                        }
                    }
                 }"
                 x-init="initMap()"
            >
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <i class="ph-bold ph-map-pin text-primary text-base"></i>
                        <h4 class="text-xs font-bold text-slate-800 dark:text-slate-100 uppercase tracking-wider">Lokasi GPS & Visualisasi Radius Absensi</h4>
                    </div>
                    <button
                        type="button"
                        @click="getGps()"
                        class="inline-flex items-center gap-1 text-xs font-bold text-primary hover:underline cursor-pointer"
                    >
                        <i class="ph-bold ph-crosshair"></i>
                        <span x-text="gettingGps ? 'Mendeteksi...' : 'Deteksi Koordinat Saya'"></span>
                    </button>
                </div>

                <!-- Container Interactive Map Leaflet.js -->
                <div x-ref="storeMapContainer" class="w-full h-52 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-inner"></div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Latitude</label>
                        <input
                            type="number"
                            step="any"
                            wire:model.live="latitude"
                            placeholder="-0.03470087552402962"
                            class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-mono text-slate-900 dark:text-white outline-none"
                        >
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Longitude</label>
                        <input
                            type="number"
                            step="any"
                            wire:model.live="longitude"
                            placeholder="109.33239215349418"
                            class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-mono text-slate-900 dark:text-white outline-none"
                        >
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-slate-600 dark:text-slate-400 mb-1">Radius Toleransi (Meter)</label>
                        <input
                            type="number"
                            wire:model.live="attendance_radius_meters"
                            placeholder="100"
                            class="w-full px-3 py-1.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold text-slate-900 dark:text-white outline-none"
                        >
                    </div>
                </div>
                <p class="text-[10px] text-slate-400">
                    Geser pin marker pada peta atau klik "Deteksi Koordinat Saya" untuk memperbarui lokasi toko. Lingkaran biru memvisualisasikan batas radius absensi karyawan.
                </p>
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
                <x-pos.utility.button
                    type="submit"
                    variant="primary"
                    size="base"
                    icon="ph-floppy-disk"
                >
                    Simpan Profil Toko
                </x-pos.utility.button>
            </div>
        </form>
    </div>
</div>
