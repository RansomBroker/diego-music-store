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
            <x-pos.utility.button
                type="button"
                variant="secondary"
                size="sm"
                icon="ph-crosshair"
                @click="getGps()"
            >
                <span x-text="gettingGps ? 'Mendeteksi...' : 'Gunakan Lokasi Saya'"></span>
            </x-pos.utility.button>
        </div>

        <!-- Leaflet JS Interactive Map Container -->
        <div x-ref="radiusMapContainer" class="w-full h-64 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-inner relative" style="min-height: 260px; z-index: 10;"></div>

        <!-- Form Inputs -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <x-pos.form.input
                label="Latitude (Auto Pick/GPS)"
                type="number"
                step="any"
                wire:model.live="latitude"
                readonly
                icon="ph-map-pin"
                size="sm"
                class="cursor-not-allowed bg-slate-100 dark:bg-slate-800/60 "
                placeholder="-0.03470087552402962"
                required
            />
            <x-pos.form.input
                label="Longitude (Auto Pick/GPS)"
                type="number"
                step="any"
                wire:model.live="longitude"
                readonly
                icon="ph-map-pin"
                size="sm"
                class="cursor-not-allowed bg-slate-100 dark:bg-slate-800/60 "
                placeholder="109.33239215349418"
                required
            />
            <x-pos.form.input
                label="Radius Toleransi"
                type="number"
                min="1"
                wire:model.live="attendance_radius_meters"
                icon="ph-ruler"
                suffix="Meter"
                size="sm"
                placeholder="100"
                required
            />
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                type="button"
                variant="secondary"
                size="sm"
                wire:click="$set('showEditModal', false)"
            >
                Batal
            </x-pos.utility.button>
            <x-pos.utility.button
                type="button"
                variant="primary"
                size="sm"
                icon="ph-check"
                wire:click="saveRadius"
            >
                Simpan Radius Cabang
            </x-pos.utility.button>
        </div>
    </div>
</x-pos.modal>
