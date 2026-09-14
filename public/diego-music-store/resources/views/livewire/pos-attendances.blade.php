<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">

    <!-- Sidebar -->
    <x-pos-page::sidebar :selectedLogoUrl="$selectedLogoUrl" />

    <!-- Main Content -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Navbar -->
        <x-pos.navbar
            pageTitle="Presensi Karyawan"
            backLabel="Dashboard"
        />

        <!-- Main Scrollable Area -->
        <div class="flex-1 overflow-y-auto no-scrollbar p-6">
            <div class="w-full space-y-6">

                <!-- Page Header & Quick Actions -->
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
                                        <span class="text-slate-650 dark:text-slate-300 font-bold">Presensi Karyawan</span>
                                    </div>
                                </li>
                            </ol>
                        </nav>
                        <h1 class="text-2xl font-black text-slate-900 dark:text-white leading-tight">Presensi & Off Day Karyawan</h1>
                    </div>

                    <!-- Quick Clock-In / Clock-Out & Backdate Buttons -->
                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            wire:click="openClockModal('in')"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition cursor-pointer"
                        >
                            <i class="ph-bold ph-sign-in text-base"></i>
                            <span>Clock In (Masuk)</span>
                        </button>
                        <button
                            wire:click="openClockModal('out')"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition cursor-pointer"
                        >
                            <i class="ph-bold ph-sign-out text-base"></i>
                            <span>Clock Out (Pulang)</span>
                        </button>
                        <button
                            wire:click="openBackdateModal()"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-purple-600 hover:bg-purple-700 text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition cursor-pointer"
                            title="Ajukan presensi susulan untuk tanggal lampau"
                        >
                            <i class="ph-bold ph-clock-counter-clockwise text-base"></i>
                            <span>Request Backdate</span>
                        </button>
                        <button
                            wire:click="openRecordModal()"
                            class="inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-primary hover:bg-primaryDark text-white text-xs font-bold rounded-xl shadow-sm hover:shadow transition cursor-pointer"
                        >
                            <i class="ph-bold ph-calendar-plus text-base"></i>
                            <span>Catat Off Day / Izin</span>
                        </button>
                    </div>
                </div>

                <!-- OWNER ONLY: PERSATUAN BACKDATE PRESENSI PENDING -->
                @if ($isOwnerUser && count($pendingBackdateRequests) > 0)
                    <div class="bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800/80 rounded-2xl p-5 shadow-sm space-y-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2.5">
                                <div class="w-9 h-9 rounded-xl bg-amber-500 text-white flex items-center justify-center font-bold">
                                    <i class="ph-bold ph-hourglass text-lg"></i>
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-amber-900 dark:text-amber-200">
                                        Persetujuan Request Presensi Susulan (Backdate)
                                    </h3>
                                    <p class="text-xs text-amber-700 dark:text-amber-400">
                                        Terdapat {{ count($pendingBackdateRequests) }} permohonan presensi susulan dari karyawan yang memerlukan verifikasi Owner
                                    </p>
                                </div>
                            </div>
                            <span class="px-3 py-1 bg-amber-500 text-white rounded-full text-xs font-black">
                                {{ count($pendingBackdateRequests) }} Pending
                            </span>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @foreach ($pendingBackdateRequests as $req)
                                <div class="p-4 bg-white dark:bg-slate-900 rounded-xl border border-amber-200 dark:border-amber-800/60 shadow-sm space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h4 class="text-xs font-black text-slate-800 dark:text-slate-100">{{ $req->employee->name }}</h4>
                                            <p class="text-[10px] text-slate-400 font-mono">{{ $req->employee->nik }} &bull; {{ $req->branch?->name ?: 'Cabang Utama' }}</p>
                                        </div>
                                        <span class="px-2.5 py-1 bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 rounded-lg text-[10px] font-black">
                                            {{ $req->requested_date->format('d M Y') }}
                                        </span>
                                    </div>

                                    <div class="text-xs space-y-1 text-slate-600 dark:text-slate-300 bg-slate-50 dark:bg-slate-800 p-2.5 rounded-lg border border-slate-100 dark:border-slate-700">
                                        <div><strong>Jam Diminta:</strong> <span class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">In: {{ substr($req->clock_in, 0, 5) }}</span> &bull; <span class="font-mono text-amber-600 dark:text-amber-400 font-bold">Out: {{ substr($req->clock_out ?: '17:00', 0, 5) }}</span></div>
                                        <div><strong>Alasan:</strong> {{ $req->reason }}</div>
                                    </div>

                                    <div class="flex items-center justify-end gap-2 pt-1">
                                        <button
                                            wire:click="processBackdateApproval({{ $req->id }}, 'reject')"
                                            class="px-3 py-1.5 bg-rose-100 hover:bg-rose-200 text-rose-700 dark:bg-rose-950 dark:hover:bg-rose-900 dark:text-rose-300 text-xs font-bold rounded-lg transition cursor-pointer"
                                        >
                                            <i class="ph-bold ph-x mr-1"></i> Tolak
                                        </button>
                                        <button
                                            wire:click="processBackdateApproval({{ $req->id }}, 'approve')"
                                            class="px-3.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-black rounded-lg shadow-sm transition cursor-pointer"
                                        >
                                            <i class="ph-bold ph-check mr-1"></i> Setujui & Catat Presensi
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

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
                                placeholder="Cari nama karyawan..."
                                class="w-full pl-9 pr-4 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-slate-100 placeholder-slate-400 focus:border-primary focus:outline-none"
                            >
                        </div>

                        <!-- Filters: Month & Branch -->
                        <div class="flex items-center gap-3">
                            <div>
                                <input
                                    type="month"
                                    wire:model.live="filterMonth"
                                    class="px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                                >
                            </div>
                            <div>
                                <select
                                    wire:model.live="filterBranchId"
                                    class="px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                                >
                                    <option value="">Semua Cabang</option>
                                    @foreach ($branches as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Table -->
                    <x-pos.table.container>
                        <x-pos.table>
                            <thead class="bg-slate-50 dark:bg-slate-800/40 border-b border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200 font-medium text-xs">
                                <tr>
                                    <x-pos.table.th>Tanggal</x-pos.table.th>
                                    <x-pos.table.th>Karyawan</x-pos.table.th>
                                    <x-pos.table.th>Cabang</x-pos.table.th>
                                    <x-pos.table.th>Clock In / Out</x-pos.table.th>
                                    <x-pos.table.th>Status & Off Day</x-pos.table.th>
                                    <x-pos.table.th>Radius GPS</x-pos.table.th>
                                    <x-pos.table.th>Bukti Foto Selfie</x-pos.table.th>
                                    <x-pos.table.th>Catatan</x-pos.table.th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-200 dark:divide-slate-800 text-slate-700 dark:text-slate-300 text-xs">
                                @forelse ($attendances as $row)
                                    @php
                                        $emp = $row->employee;
                                        $usedOff = $emp ? $emp->used_off_days_this_month : 0;
                                        $quotaOff = $emp ? $emp->monthly_off_days_quota : 4;
                                        $isOver = $emp ? $emp->is_off_days_over_quota : false;
                                        $overCount = $emp ? $emp->off_days_over_count : 0;
                                    @endphp
                                    <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-800/50 transition-colors">
                                        <x-pos.table.td class="font-semibold text-slate-900 dark:text-slate-100">
                                            {{ $row->date->format('d M Y') }}
                                        </x-pos.table.td>
                                        <x-pos.table.td class="font-bold text-slate-900 dark:text-slate-100">
                                            {{ $emp->name ?? '-' }}
                                            <div class="text-[10px] text-slate-400 font-mono">{{ $emp->nik ?? '' }}</div>
                                        </x-pos.table.td>
                                        <x-pos.table.td>{{ $row->branch->name ?? 'Cabang Utama' }}</x-pos.table.td>
                                        <x-pos.table.td>
                                            <div class="space-y-0.5">
                                                <div class="font-mono text-emerald-600 dark:text-emerald-400 font-bold">
                                                    In: {{ $row->clock_in ? $row->clock_in->format('H:i:s') : '-' }}
                                                </div>
                                                <div class="font-mono text-amber-600 dark:text-amber-400 font-bold">
                                                    Out: {{ $row->clock_out ? $row->clock_out->format('H:i:s') : '-' }}
                                                </div>
                                            </div>
                                        </x-pos.table.td>
                                        <x-pos.table.td>
                                            <div class="space-y-1">
                                                @if ($row->status === 'hadir')
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 uppercase">
                                                        Hadir
                                                    </span>
                                                @elseif ($row->status === 'off_day')
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300 uppercase">
                                                        Off Day
                                                    </span>
                                                @elseif ($row->status === 'izin')
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 dark:bg-amber-950 text-amber-700 dark:text-amber-300 uppercase">
                                                        Izin
                                                    </span>
                                                @elseif ($row->status === 'sakit')
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 dark:bg-purple-950 text-purple-700 dark:text-purple-300 uppercase">
                                                        Sakit
                                                    </span>
                                                @else
                                                    <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 uppercase">
                                                        Alpha
                                                    </span>
                                                @endif

                                                <div class="text-[10px] text-slate-400">
                                                    Off: <span class="{{ $isOver ? 'text-rose-500 font-bold' : '' }}">{{ $usedOff }}/{{ $quotaOff }} Hari</span>
                                                </div>
                                            </div>
                                        </x-pos.table.td>
                                        <x-pos.table.td>
                                            @if ($row->latitude && $row->longitude)
                                                @if ($row->is_out_of_radius)
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 dark:bg-rose-950 text-rose-700 dark:text-rose-300 flex items-center gap-1 w-fit" title="{{ $row->distance_meters }} meter dari cabang">
                                                        <i class="ph-bold ph-warning"></i>
                                                        Luar Radius ({{ $row->distance_meters }}m)
                                                    </span>
                                                @else
                                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 flex items-center gap-1 w-fit" title="{{ $row->distance_meters }} meter dari cabang">
                                                        <i class="ph-bold ph-check-circle"></i>
                                                        Dalam Radius ({{ $row->distance_meters }}m)
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-slate-400 text-[11px]">-</span>
                                            @endif
                                        </x-pos.table.td>
                                        <x-pos.table.td>
                                            <div class="flex items-center gap-1">
                                                @if ($row->clock_in_photo_path)
                                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($row->clock_in_photo_path) }}" target="_blank" title="Foto Clock In">
                                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($row->clock_in_photo_path) }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700 hover:scale-110 transition-transform">
                                                    </a>
                                                @endif
                                                @if ($row->clock_out_photo_path)
                                                    <a href="{{ \Illuminate\Support\Facades\Storage::url($row->clock_out_photo_path) }}" target="_blank" title="Foto Clock Out">
                                                        <img src="{{ \Illuminate\Support\Facades\Storage::url($row->clock_out_photo_path) }}" class="w-8 h-8 rounded-lg object-cover border border-slate-200 dark:border-slate-700 hover:scale-110 transition-transform">
                                                    </a>
                                                @endif
                                                @if (!$row->clock_in_photo_path && !$row->clock_out_photo_path)
                                                    <span class="text-slate-400 text-[11px]">-</span>
                                                @endif
                                            </div>
                                        </x-pos.table.td>
                                        <x-pos.table.td class="max-w-xs truncate text-slate-500">
                                            {{ $row->notes ?: '-' }}
                                        </x-pos.table.td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-12 text-center text-slate-400 dark:text-slate-500">
                                            <div class="flex flex-col items-center justify-center gap-2">
                                                <i class="ph ph-calendar text-4xl text-slate-300 dark:text-slate-600"></i>
                                                <span class="text-sm font-medium">Belum ada riwayat presensi untuk periode ini</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </x-pos.table>
                    </x-pos.table.container>

                    <!-- Pagination -->
                    @if ($attendances->hasPages())
                        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-900">
                            {{ $attendances->links() }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </main>

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
                        <button type="button" @click="startWebcam()" class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-bold">
                            Coba Buka Kamera
                        </button>
                    </div>
                </div>

                <!-- Camera Controls Buttons -->
                <div class="flex items-center justify-center gap-3">
                    <button
                        x-show="hasCameraPermission && !snapshotDataUrl"
                        type="button"
                        @click="takeSnapshot()"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-2 cursor-pointer"
                    >
                        <i class="ph-bold ph-camera text-base"></i>
                        <span>Ambil Foto Selfie</span>
                    </button>

                    <button
                        x-show="snapshotDataUrl"
                        type="button"
                        @click="retakeSnapshot()"
                        class="px-4 py-2 bg-slate-600 hover:bg-slate-700 text-white text-xs font-bold rounded-xl shadow-md flex items-center gap-2 cursor-pointer"
                    >
                        <i class="ph-bold ph-arrows-counter-clockwise text-base"></i>
                        <span>Foto Ulang</span>
                    </button>
                </div>
            </div>

            <!-- Status GPS Indicator -->
            <div class="p-3 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200 dark:border-slate-700 flex items-center justify-between text-xs">
                <span class="font-bold text-slate-700 dark:text-slate-300 flex items-center gap-1.5">
                    <i class="ph-bold ph-navigation-arrow text-primary"></i>
                    Status Verifikasi GPS
                </span>
                <span x-text="locationStatus" class="font-mono text-[11px] text-slate-600 dark:text-slate-400 font-bold"></span>
            </div>

            <!-- Catatan (Opsional) -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Catatan / Keterangan Kerja (Opsional)
                </label>
                <textarea
                    wire:model="notes"
                    rows="2"
                    placeholder="Isikan catatan lokasi atau aktivitas..."
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-slate-100 focus:border-primary outline-none"
                ></textarea>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button
                    type="button"
                    @click="stopWebcam(); $set('showClockModal', false);"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Batal
                </button>

                @if ($clockType === 'in')
                    <button
                        type="button"
                        wire:click="quickClockIn"
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150 cursor-pointer"
                    >
                        Konfirmasi Clock In
                    </button>
                @else
                    <button
                        type="button"
                        wire:click="quickClockOut"
                        class="px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150 cursor-pointer"
                    >
                        Konfirmasi Clock Out
                    </button>
                @endif
            </div>
        </div>
    </x-pos.modal>

    <!-- Modal Catat Status Presensi / Off Day -->
    <x-pos.modal
        wire:model="showModal"
        title="Catat Status Presensi / Off Day"
        subtitle="Pilih karyawan, tanggal, dan status presensi yang diajukan"
        icon="ph-calendar-plus"
        maxWidth="md"
    >
        <form wire:submit.prevent="saveRecord" class="space-y-4">
            <!-- Pilih Karyawan -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Pilih Karyawan <span class="text-rose-500">*</span>
                </label>
                <select
                    wire:model="selectedEmployeeId"
                    required
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary focus:outline-none"
                >
                    <option value="">-- Pilih Karyawan --</option>
                    @foreach ($employees as $e)
                        <option value="{{ $e->id }}">{{ $e->name }} ({{ $e->nik }})</option>
                    @endforeach
                </select>
                @error('selectedEmployeeId') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Tanggal -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Tanggal Presensi <span class="text-rose-500">*</span>
                </label>
                <input
                    type="date"
                    wire:model="attendanceDate"
                    required
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary focus:outline-none"
                >
                @error('attendanceDate') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Status Presensi -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Status Presensi <span class="text-rose-500">*</span>
                </label>
                <select
                    wire:model="status"
                    required
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-sm text-slate-900 dark:text-slate-100 focus:border-primary focus:outline-none"
                >
                    <option value="off_day">Off Day (Hari Libur Jatah)</option>
                    <option value="hadir">Hadir</option>
                    <option value="izin">Izin</option>
                    <option value="sakit">Sakit</option>
                    <option value="alpha">Alpha (Tanpa Keterangan)</option>
                </select>
                @error('status') <span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span> @enderror
            </div>

            <!-- Catatan -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 dark:text-slate-300 mb-1">
                    Catatan / Alasan (Opsional)
                </label>
                <textarea
                    wire:model="notes"
                    rows="2"
                    placeholder="Isikan keterangan..."
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-lg text-xs text-slate-900 dark:text-slate-100 focus:border-primary focus:outline-none"
                ></textarea>
            </div>

            <!-- Footer Actions -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button
                    type="button"
                    wire:click="$set('showModal', false)"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-sm font-semibold rounded-lg transition-colors cursor-pointer"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 bg-primary hover:bg-primaryDark text-white text-sm font-semibold rounded-lg shadow-sm transition duration-150 cursor-pointer"
                >
                    Simpan Presensi
                </button>
            </div>
        </form>
    </x-pos.modal>

    <!-- MODAL POPUP: REQUEST PRESENSI SUSULAN (BACKDATE) -->
    <x-pos.modal wire:model="showBackdateModal" title="Form Request Presensi Susulan (Backdate)" maxWidth="lg">
        <form wire:submit.prevent="submitBackdateRequest" class="space-y-4">
            <div class="p-3 bg-purple-50 dark:bg-purple-950/40 border border-purple-200 dark:border-purple-800 rounded-xl text-xs text-purple-800 dark:text-purple-300">
                💡 <strong>Informasi:</strong> Pengajuan ini akan dikirim ke Owner/Admin untuk diverifikasi. Setelah disetujui, presensi tanggal yang Anda ajukan akan otomatis tercatat sebagai "Hadir".
            </div>

            <!-- Tanggal Susulan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Tanggal Presensi Susulan <span class="text-rose-500">*</span>
                </label>
                <input
                    type="date"
                    wire:model="backdateDate"
                    max="{{ now()->format('Y-m-d') }}"
                    required
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:border-purple-500 focus:outline-none"
                >
            </div>

            <!-- Grid Jam Masuk & Pulang -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Jam Masuk (Clock In) <span class="text-rose-500">*</span>
                    </label>
                    <input
                        type="time"
                        wire:model="backdateClockIn"
                        required
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:border-purple-500 focus:outline-none"
                    >
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                        Jam Pulang (Clock Out)
                    </label>
                    <input
                        type="time"
                        wire:model="backdateClockOut"
                        class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-sm text-slate-900 dark:text-slate-100 focus:border-purple-500 focus:outline-none"
                    >
                </div>
            </div>

            <!-- Alasan Pengajuan -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Alasan / Keterangan Kendala <span class="text-rose-500">*</span>
                </label>
                <textarea
                    wire:model="backdateReason"
                    rows="3"
                    required
                    placeholder="Jelaskan alasan mengapa lupa absen atau kendala saat bertugas..."
                    class="w-full px-3 py-2 bg-white dark:bg-slate-950 border border-slate-300 dark:border-slate-700 rounded-xl text-xs text-slate-900 dark:text-slate-100 focus:border-purple-500 focus:outline-none"
                ></textarea>
            </div>

            <!-- Upload Foto Bukti (Opsional) -->
            <div>
                <label class="block text-xs font-bold text-slate-700 dark:text-slate-300 mb-1">
                    Upload Foto Bukti (Opsional)
                </label>
                <input
                    type="file"
                    wire:model="backdatePhoto"
                    accept="image/*"
                    class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-purple-100 file:text-purple-700 hover:file:bg-purple-200 cursor-pointer"
                >
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200 dark:border-slate-800">
                <button
                    type="button"
                    wire:click="$set('showBackdateModal', false)"
                    class="px-4 py-2 bg-slate-100 dark:bg-slate-800 hover:bg-slate-200 dark:hover:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-bold rounded-xl transition"
                >
                    Batal
                </button>
                <button
                    type="submit"
                    class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-xs font-black rounded-xl shadow-sm transition"
                >
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </x-pos.modal>
</div>
