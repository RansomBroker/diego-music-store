<!-- Modal Clock In / Clock Out dengan Mandatory Live Webcam Selfie & Leaflet.js Map -->
<x-pos.modal
    wire:model="showClockModal"
    :title="$clockType === 'in' ? 'Clock In (Absen Masuk)' : 'Clock Out (Absen Pulang)'"
    subtitle="Ambil foto selfie kamera & verifikasi koordinat lokasi cabang"
    :icon="$clockType === 'in' ? 'ph-sign-in' : 'ph-sign-out'"
    maxWidth="lg"
>
    <div x-data="{
        hasCameraPermission: false,
        cameraError: '',
        cameraStream: null,
        snapshotDataUrl: null,
        gettingLocation: false,
        locationStatus: '',
        map: null,
        branchLat: {{ $selectedBranch?->latitude !== null ? $selectedBranch->latitude : -0.03470087552402962 }},
        branchLng: {{ $selectedBranch?->longitude !== null ? $selectedBranch->longitude : 109.33239215349418 }},
        branchRadius: {{ $selectedBranch?->attendance_radius_meters ?: 100 }},

        startWebcam() {
            this.cameraError = '';
            this.snapshotDataUrl = null;
            $wire.webcamDataUrl = null;

            if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user', width: 640, height: 480 } })
                    .then((stream) => {
                        this.cameraStream = stream;
                        this.hasCameraPermission = true;
                        if (this.$refs.webcamVideo) {
                            this.$refs.webcamVideo.srcObject = stream;
                        }
                    })
                    .catch((err) => {
                        this.hasCameraPermission = false;
                        this.cameraError = 'Izin kamera ditolak / tidak ditemukan. Presensi diblokir sampai izin kamera diaktifkan.';
                    });
            } else {
                this.cameraError = 'Browser Anda tidak mendukung akses kamera webcam.';
            }
        },

        stopWebcam() {
            if (this.cameraStream) {
                this.cameraStream.getTracks().forEach(track => track.stop());
                this.cameraStream = null;
            }
        },

        takeSnapshot() {
            if (!this.hasCameraPermission || !this.$refs.webcamVideo) return;
            const video = this.$refs.webcamVideo;
            const canvas = document.createElement('canvas');
            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
            this.snapshotDataUrl = canvas.toDataURL('image/jpeg', 0.85);
            $wire.set('webcamDataUrl', this.snapshotDataUrl);
        },

        retakeSnapshot() {
            this.snapshotDataUrl = null;
            $wire.set('webcamDataUrl', null);
        },

        initMapAndLocation() {
            $wire.$watch('showClockModal', (val) => {
                if (val) {
                    this.startWebcam();
                    this.tryInitLocationAndMap();
                } else {
                    this.stopWebcam();
                }
            });
            if ($wire.showClockModal) {
                this.startWebcam();
                this.tryInitLocationAndMap();
            }
        },

        tryInitLocationAndMap() {
            this.gettingLocation = true;
            this.locationStatus = 'Mendeteksi lokasi GPS...';

            if ('geolocation' in navigator) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    $wire.set('userLatitude', pos.coords.latitude);
                    $wire.set('userLongitude', pos.coords.longitude);
                    this.gettingLocation = false;
                    this.locationStatus = 'GPS Terdeteksi: ' + pos.coords.latitude.toFixed(5) + ', ' + pos.coords.longitude.toFixed(5);
                }, (err) => {
                    this.gettingLocation = false;
                    this.locationStatus = 'GPS Ditolak: ' + err.message;
                }, { enableHighAccuracy: true });
            } else {
                this.gettingLocation = false;
                this.locationStatus = 'GPS Tidak Didukung Browser';
            }
        }
    }"
    x-init="initMapAndLocation();"
    class="space-y-4"
    >

        @if (!empty($errorMessage))
            <div class="p-3.5 bg-rose-50 dark:bg-rose-950/60 border border-rose-200 dark:border-rose-800 rounded-2xl flex items-start gap-3">
                <i class="ph-bold ph-warning-circle text-rose-600 text-xl flex-shrink-0 mt-0.5"></i>
                <div class="text-xs">
                    <span class="font-extrabold text-rose-800 dark:text-rose-300 block mb-0.5">Gagal Presensi!</span>
                    <p class="text-rose-700 dark:text-rose-300">{{ $errorMessage }}</p>
                </div>
            </div>
        @endif

        <!-- Mandatory Camera Warning Banner -->
        <div x-show="!hasCameraPermission && cameraError" class="p-3.5 bg-rose-50 dark:bg-rose-950/40 border border-rose-200 dark:border-rose-800/60 rounded-2xl flex items-start gap-3">
            <i class="ph-bold ph-camera-slash text-rose-600 text-xl flex-shrink-0 mt-0.5"></i>
            <div class="text-xs">
                <span class="font-extrabold text-rose-800 dark:text-rose-300 block mb-0.5">IZIN KAMERA WAJIB DIAKTIFKAN!</span>
                <p x-text="cameraError" class="text-rose-700 dark:text-rose-400"></p>
            </div>
        </div>

        <!-- Live Webcam Stream & Selfie Snapshot Container -->
        <div class="space-y-2">
            <label class="block text-xs font-bold text-slate-700 dark:text-slate-300">
                Foto Selfie Presensi (Kamera Wajib) <span class="text-rose-500">*</span>
            </label>

            <div class="relative w-full h-56 bg-slate-900 rounded-2xl overflow-hidden flex items-center justify-center border border-slate-700">
                <!-- Live Video Feed -->
                <video
                    x-ref="webcamVideo"
                    x-show="hasCameraPermission && !snapshotDataUrl"
                    autoplay
                    playsinline
                    muted
                    class="w-full h-full object-cover"
                ></video>

                <!-- Taken Snapshot Preview -->
                <template x-if="snapshotDataUrl">
                    <img :src="snapshotDataUrl" class="w-full h-full object-cover">
                </template>

                <!-- Camera Placeholder if blocked -->
                <div x-show="!hasCameraPermission" class="text-center p-4 text-slate-400 space-y-2">
                    <i class="ph-bold ph-camera-slash text-4xl text-rose-500"></i>
                    <p class="text-xs">Aktifkan izin kamera pada browser Anda untuk dapat melakukan selfie presensi.</p>
                    <x-pos.utility.button
                        variant="primary"
                        size="sm"
                        @click="startWebcam()"
                    >
                        Coba Buka Kamera
                    </x-pos.utility.button>
                </div>
            </div>

            <!-- Camera Controls Buttons -->
            <div class="flex items-center justify-center gap-3">
                <x-pos.utility.button
                    x-show="hasCameraPermission && !snapshotDataUrl"
                    variant="success"
                    size="sm"
                    icon="ph-camera"
                    @click="takeSnapshot()"
                >
                    Ambil Foto Selfie
                </x-pos.utility.button>

                <x-pos.utility.button
                    x-show="snapshotDataUrl"
                    variant="secondary"
                    size="sm"
                    icon="ph-arrows-counter-clockwise"
                    @click="retakeSnapshot()"
                >
                    Foto Ulang
                </x-pos.utility.button>
            </div>
        </div>

        <!-- Catatan (Opsional) -->
        <div>
            <label class="block text-xs font-black uppercase tracking-wider text-slate-800 dark:text-slate-200 mb-1.5">
                Catatan / Keterangan Kerja (Opsional)
            </label>
            <textarea
                wire:model="notes"
                rows="2"
                placeholder="Isikan catatan lokasi atau aktivitas..."
                class="w-full px-3.5 py-2.5 bg-white dark:bg-slate-900 border border-slate-300 dark:border-slate-700 rounded-xl outline-hidden font-bold text-sm focus:ring-2 focus:ring-primary dark:focus:ring-blue-500 text-slate-800 dark:text-slate-100"
            ></textarea>
        </div>

        <!-- Footer Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
            <x-pos.utility.button
                variant="secondary"
                size="sm"
                @click="stopWebcam(); $set('showClockModal', false);"
            >
                Batal
            </x-pos.utility.button>

            @if ($clockType === 'in')
                <x-pos.utility.button
                    variant="success"
                    size="sm"
                    icon="ph-sign-in"
                    wire:click="quickClockIn"
                >
                    Konfirmasi Clock In
                </x-pos.utility.button>
            @else
                <x-pos.utility.button
                    variant="warning"
                    size="sm"
                    icon="ph-sign-out"
                    wire:click="quickClockOut"
                >
                    Konfirmasi Clock Out
                </x-pos.utility.button>
            @endif
        </div>
    </div>
</x-pos.modal>
