<div
    x-data="{
        map: null,
        marker: null,
        circle: null,
        getting: false,

        init() {
            setTimeout(() => this.renderMap(), 300);
        },

        findInput(fieldName) {
            return document.querySelector('input[id*=' + fieldName + ']') ||
                   document.querySelector('input[name*=' + fieldName + ']');
        },

        getVal(field) {
            const el = this.findInput(field);
            if (el && el.value !== '' && el.value !== null) {
                return el.value;
            }
            return null;
        },

        setVal(field, val) {
            const el = this.findInput(field);
            if (el) {
                el.value = val;
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        },

        renderMap() {
            const container = this.$refs.filamentMapContainer;
            if (!container || !window.L) {
                setTimeout(() => this.renderMap(), 150);
                return;
            }
            if (this.map) {
                try { this.map.remove(); } catch(e) {}
            }

            let lat = parseFloat(this.getVal('latitude')) || -0.03470087552402962;
            let lng = parseFloat(this.getVal('longitude')) || 109.33239215349418;
            let radius = parseInt(this.getVal('attendance_radius_meters')) || 100;

            this.map = L.map(container, {
                center: [lat, lng],
                zoom: 16
            });

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; OpenStreetMap'
            }).addTo(this.map);

            this.circle = L.circle([lat, lng], {
                color: '#3b82f6',
                fillColor: '#93c5fd',
                fillOpacity: 0.35,
                radius: radius
            }).addTo(this.map);

            this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map)
                .bindPopup('<b>Titik Cabang</b><br>Geser pin atau klik peta untuk ubah koordinat')
                .openPopup();

            const onLocationPicked = (latVal, lngVal) => {
                const roundedLat = parseFloat(latVal.toFixed(7));
                const roundedLng = parseFloat(lngVal.toFixed(7));
                this.setVal('latitude', roundedLat);
                this.setVal('longitude', roundedLng);
                if (this.circle) this.circle.setLatLng([roundedLat, roundedLng]);
            };

            this.marker.on('dragend', (e) => {
                const pos = e.target.getLatLng();
                onLocationPicked(pos.lat, pos.lng);
            });

            this.map.on('click', (e) => {
                const pos = e.latlng;
                this.marker.setLatLng(pos);
                onLocationPicked(pos.lat, pos.lng);
            });

            setTimeout(() => {
                if (this.map) this.map.invalidateSize();
            }, 250);

            const radiusEl = this.findInput('attendance_radius_meters');
            if (radiusEl) {
                radiusEl.addEventListener('input', (e) => {
                    let newRadius = parseInt(e.target.value) || 100;
                    if (this.circle) this.circle.setRadius(newRadius);
                });
            }

            setInterval(() => {
                let currentVal = this.getVal('attendance_radius_meters');
                let newRadius = parseInt(currentVal) || 100;
                if (this.circle && this.circle.getRadius() !== newRadius) {
                    this.circle.setRadius(newRadius);
                }
            }, 200);
        },

        getGps() {
            this.getting = true;
            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    const latVal = parseFloat(pos.coords.latitude.toFixed(7));
                    const lngVal = parseFloat(pos.coords.longitude.toFixed(7));
                    this.setVal('latitude', latVal);
                    this.setVal('longitude', lngVal);
                    this.getting = false;
                    if (this.marker && this.map) {
                        this.marker.setLatLng([latVal, lngVal]);
                        this.map.setView([latVal, lngVal], 16);
                        if (this.circle) this.circle.setLatLng([latVal, lngVal]);
                    }
                }, (err) => {
                    this.getting = false;
                    alert('Gagal mengambil GPS: ' + err.message);
                }, { enableHighAccuracy: true });
            }
        }
    }"
    class="space-y-3"
>
    <div class="flex items-center justify-between text-xs font-bold text-gray-700 dark:text-gray-300">
        <span class="flex items-center gap-1.5">
            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            Visualisasi Peta Interaktif & Radius Circle
        </span>
        <button
            type="button"
            @click="getGps()"
            class="text-xs font-bold text-blue-600 hover:underline cursor-pointer"
        >
            <span x-text="getting ? 'Mendeteksi...' : 'Gunakan Koordinat GPS Saya'"></span>
        </button>
    </div>

    <!-- Map Container -->
    <div x-ref="filamentMapContainer" class="w-full rounded-xl border border-gray-300 dark:border-gray-700 overflow-hidden shadow-inner bg-gray-100 dark:bg-gray-800" style="height: 300px; min-height: 280px; z-index: 10;"></div>
</div>
