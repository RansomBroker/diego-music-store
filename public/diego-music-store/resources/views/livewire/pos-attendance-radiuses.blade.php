<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Radius Presensi Cabang"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header & Breadcrumbs -->
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
                                        <a href="/pos/employees" class="hover:text-primary dark:hover:text-blue-400 transition-colors">Karyawan</a>
                                    </div>
                                </li>
                                <li>
                                    <div class="flex items-center">
                                        <i class="ph ph-caret-right text-[10px] text-slate-350 dark:text-slate-650 mx-1"></i>
                                        <span class="text-slate-650 dark:text-slate-300 font-bold">Radius Presensi</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Pengaturan Radius Presensi Cabang</h1>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Tentukan titik koordinat GPS dan radius toleransi jarak (meter) untuk presensi karyawan per cabang</p>
                    </div>
                </div>

                <!-- Table Card Wrapper -->
                <div class="bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm rounded-xl overflow-hidden transition-colors duration-200">

                    <!-- Toolbar (Filters) -->
                    <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-white dark:bg-slate-900">
                        <!-- Search Input -->
                        <div class="relative w-full sm:max-w-xs">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                <i class="ph ph-magnifying-glass text-slate-400 dark:text-slate-550 text-base"></i>
                            </span>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Cari nama cabang atau kota..."
                                class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary focus:outline-none"
                            >
                        </div>
                    </div>

                    <!-- Table -->
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium text-xs">
                                <tr>
                                    <x-pos.table.th>Nama Cabang / Toko</x-pos.table.th>
                                    <x-pos.table.th>Alamat Fisik</x-pos.table.th>
                                    <x-pos.table.th>Koordinat GPS (Lat, Lng)</x-pos.table.th>
                                    <x-pos.table.th>Radius Toleransi (Meter)</x-pos.table.th>
                                    <x-pos.table.th>Status Operasional</x-pos.table.th>
                                    <x-pos.table.th class="text-right">Aksi</x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300 text-xs">
                                @forelse ($branches as $b)
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                        <x-pos.table.td class="font-bold text-slate-900 dark:text-slate-100">
                                            {{ $b->name }}
                                            <div class="text-[10px] text-slate-400 font-normal">{{ $b->store_name ?: $b->name }}</div>
                                        </x-pos.table.td>
                                        <x-pos.table.td class="max-w-xs truncate text-slate-500">
                                            {{ $b->address ?: 'Alamat belum diatur' }}
                                        </x-pos.table.td>
                                        <x-pos.table.td>
                                            @if ($b->latitude !== null && $b->longitude !== null)
                                                <div class="font-mono text-xs font-semibold text-slate-800 dark:text-slate-200 flex items-center gap-1">
                                                    <i class="ph-bold ph-map-pin text-primary text-sm"></i>
                                                    {{ number_format($b->latitude, 6) }}, {{ number_format($b->longitude, 6) }}
                                                </div>
                                            @else
                                                <span class="text-amber-500 font-semibold text-[11px] flex items-center gap-1">
                                                    <i class="ph-bold ph-warning"></i> Belum Set Koordinat
                                                </span>
                                            @endif
                                        </x-pos.table.td>
                                        <x-pos.table.td>
                                            <span class="px-2.5 py-1 rounded-lg text-xs font-black bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 inline-flex items-center gap-1">
                                                <i class="ph-bold ph-ruler"></i>
                                                {{ $b->attendance_radius_meters ?: 100 }} Meter
                                            </span>
                                        </x-pos.table.td>
                                        <x-pos.table.td>
                                            @if ($b->is_active)
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 uppercase">
                                                    Aktif
                                                </span>
                                            @else
                                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 dark:bg-slate-800 text-slate-600 dark:text-slate-400 uppercase">
                                                    Nonaktif
                                                </span>
                                            @endif
                                        </x-pos.table.td>
                                        <x-pos.table.td class="text-right">
                                            <button
                                                wire:click="openEditModal({{ $b->id }})"
                                                class="inline-flex items-center justify-center gap-1 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-lg shadow-sm transition cursor-pointer"
                                                title="Edit Radius Presensi Peta Leaflet"
                                            >
                                                <i class="ph-bold ph-map-pin-line text-sm"></i>
                                                <span>Edit Radius</span>
                                            </button>
                                        </x-pos.table.td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <i class="ph ph-storefront text-4xl text-slate-300 dark:text-slate-600"></i>
                                                <span class="text-sm font-medium">Belum ada data cabang ditemukan</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    <!-- Pagination -->
                    @if ($branches->hasPages())
                        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                            {{ $branches->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>

    <!-- Modal Edit Radius Peta Leaflet.js -->
    <x-pos.modal
        wire:model="showEditModal"
        :title="'Setting Radius Peta — ' . $selectedBranchName"
        subtitle="Geser pin marker di atas peta atau atur koordinat & radius toleransi absensi (meter)"
        icon="ph-map-pin-line"
        maxWidth="lg"
    >
        <div x-data="{
            gettingGps: false,
            map: null,
            marker: null,
            circle: null,

            initMap() {
                $wire.$watch('showEditModal', (val) => {
                    if (val) {
                        // Wait for modal open transition to complete (300ms)
                        setTimeout(() => this.renderMap(), 400);
                    }
                });
            },

            renderMap() {
                const container = this.$refs.radiusMapContainer;
                if (!container) return;
                if (this.map) {
                    try { this.map.remove(); } catch(e) {}
                }

                let lat = parseFloat($wire.latitude) || -0.03470087552402962;
                let lng = parseFloat($wire.longitude) || 109.33239215349418;
                let radius = parseInt($wire.attendance_radius_meters) || 100;

                this.map = L.map(container, {
                    center: [lat, lng],
                    zoom: 16
                });

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    maxZoom: 19,
                    attribution: '© OpenStreetMap'
                }).addTo(this.map);

                this.circle = L.circle([lat, lng], {
                    color: '#3b82f6',
                    fillColor: '#93c5fd',
                    fillOpacity: 0.35,
                    radius: radius
                }).addTo(this.map);

                this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map)
                    .bindPopup('<b>Titik Lokasi Cabang</b><br>Geser pin marker untuk mengubah titik koordinat')
                    .openPopup();

                const setCoordinates = (latVal, lngVal) => {
                    const roundedLat = parseFloat(latVal.toFixed(7));
                    const roundedLng = parseFloat(lngVal.toFixed(7));
                    $wire.set('latitude', roundedLat);
                    $wire.set('longitude', roundedLng);
                    if (this.marker) this.marker.setLatLng([roundedLat, roundedLng]);
                    if (this.circle) this.circle.setLatLng([roundedLat, roundedLng]);
                };

                this.marker.on('dragend', (e) => {
                    const pos = e.target.getLatLng();
                    setCoordinates(pos.lat, pos.lng);
                });

                // Klik pada peta untuk pindahkan marker ke titik yang diklik
                this.map.on('click', (e) => {
                    const pos = e.latlng;
                    setCoordinates(pos.lat, pos.lng);
                });

                setTimeout(() => {
                    if (this.map) this.map.invalidateSize();
                }, 350);

                $wire.$watch('latitude', (val) => {
                    if (val && this.marker && this.circle) {
                        const latVal = parseFloat(val);
                        const lngVal = parseFloat($wire.longitude) || 109.33239215349418;
                        this.marker.setLatLng([latVal, lngVal]);
                        this.circle.setLatLng([latVal, lngVal]);
                    }
                });

                $wire.$watch('longitude', (val) => {
                    if (val && this.marker && this.circle) {
                        const latVal = parseFloat($wire.latitude) || -0.03470087552402962;
                        const lngVal = parseFloat(val);
                        this.marker.setLatLng([latVal, lngVal]);
                        this.circle.setLatLng([latVal, lngVal]);
                    }
                });

                $wire.$watch('attendance_radius_meters', (val) => {
                    if (this.circle) this.circle.setRadius(parseInt(val) || 100);
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
                        const latVal = parseFloat(pos.coords.latitude.toFixed(7));
                        const lngVal = parseFloat(pos.coords.longitude.toFixed(7));
                        $wire.set('latitude', latVal);
                        $wire.set('longitude', lngVal);
                        this.gettingGps = false;
                        if (this.marker && this.map) {
                            this.marker.setLatLng([latVal, lngVal]);
                            this.map.setView([latVal, lngVal], 16);
                            if (this.circle) this.circle.setLatLng([latVal, lngVal]);
                        }
                    }, (err) => {
                        this.gettingGps = false;
                        alert('Gagal mendeteksi lokasi GPS: ' + err.message);
                    }, { enableHighAccuracy: true });
                }
            }
        }" x-init="initMap()" class="space-y-4">

            <!-- Toolbar Header Map -->
            <div class="flex items-center justify-between text-xs">
                <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <i class="ph-bold ph-map-pin text-primary text-base"></i>
                    Visualisasi Peta Interaktif & Radius Circle
                </span>
                <button
                    type="button"
                    @click="getGps()"
                    class="inline-flex items-center gap-1 font-bold text-primary hover:underline cursor-pointer"
                >
                    <i class="ph-bold ph-crosshair text-sm"></i>
                    <span x-text="gettingGps ? 'Mendeteksi...' : 'Gunakan Lokasi Saya Saat Ini'"></span>
                </button>
            </div>

            <!-- Leaflet JS Interactive Map Container -->
            <div x-ref="radiusMapContainer" class="w-full h-64 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-inner relative" style="min-height: 260px; z-index: 10;"></div>

            <!-- Form Inputs -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Latitude <span class="text-rose-500">*</span> (Auto Pick Map/GPS)
                    </label>
                    <input
                        type="number"
                        step="any"
                        wire:model.live="latitude"
                        readonly
                        placeholder="-0.03470087552402962"
                        class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-mono text-slate-700 dark:text-slate-300 outline-none cursor-not-allowed"
                    >
                    @error('latitude') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Longitude <span class="text-rose-500">*</span> (Auto Pick Map/GPS)
                    </label>
                    <input
                        type="number"
                        step="any"
                        wire:model.live="longitude"
                        readonly
                        placeholder="109.33239215349418"
                        class="w-full px-3 py-2 bg-slate-100 dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-mono text-slate-700 dark:text-slate-300 outline-none cursor-not-allowed"
                    >
                    @error('longitude') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                        Radius Toleransi (Meter) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="number"
                        wire:model.live="attendance_radius_meters"
                        placeholder="100"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs font-bold text-slate-900 dark:text-slate-100 outline-none focus:border-primary"
                    >
                    @error('attendance_radius_meters') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button
                    type="button"
                    wire:click="$set('showEditModal', false)"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="button"
                    wire:click="saveRadius"
                    class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 text-sm font-bold rounded-lg shadow-sm transition duration-150 cursor-pointer"
                >
                    Simpan Radius Cabang
                </button>
            </div>
        </div>
    </x-pos.modal>
</div>
