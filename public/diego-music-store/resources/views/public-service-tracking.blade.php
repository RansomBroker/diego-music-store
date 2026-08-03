<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Service #{{ $so->ticket_code }} — {{ $so->branch->store_name ?: 'Diego Music Store' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col justify-between selection:bg-blue-500 selection:text-white">

    <!-- Top Header Navigation (Light Theme) -->
    <header class="border-b border-slate-200 bg-white/90 backdrop-blur-md sticky top-0 z-50 shadow-xs">
        <div class="max-w-3xl mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-xl bg-blue-600 flex items-center justify-center text-white font-black shadow-md shadow-blue-500/20">
                    <i class="ph ph-wrench text-2xl"></i>
                </div>
                <div>
                    <h1 class="font-extrabold text-sm tracking-wider text-slate-900 uppercase">{{ $so->branch->store_name ?: 'DIEGO MUSIC STORE' }}</h1>
                    <p class="text-[11px] text-slate-500 font-medium">Tracking Reparasi & Service Instrument</p>
                </div>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-mono font-bold bg-blue-50 text-blue-700 border border-blue-200">
                {{ $so->ticket_code }}
            </span>
        </div>
    </header>

    <!-- Main Content Container -->
    <main class="max-w-3xl mx-auto px-4 py-6 w-full flex-1 space-y-5">

        <!-- Banner Card (Light Theme) -->
        <div class="bg-white border border-slate-200 rounded-2xl p-6 shadow-sm relative overflow-hidden">
            <div class="flex items-center justify-between flex-wrap gap-2 mb-3">
                <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700 bg-blue-50 px-3 py-1 rounded-full border border-blue-200">
                    Status Terkini
                </span>
                <span class="text-xs text-slate-500 font-mono">
                    Dibuat: {{ $so->created_at->format('d M Y, H:i') }}
                </span>
            </div>
            
            <h2 class="text-2xl font-black text-slate-900 mb-1">
                {{ $so->status_label }}
            </h2>

            <p class="text-xs text-slate-600">
                Unit: <strong class="text-slate-900 font-bold">{{ $so->device_name }}</strong> &bull; Pelanggan: <span class="font-semibold text-slate-700">{{ $so->customer_name }}</span> ({{ $maskedPhone }})
            </p>
        </div>

        <!-- Visual Stepper Progress (Light Theme) -->
        @php
            $steps = [
                ['key' => 'received',      'label' => 'Diterima',         'icon' => 'ph ph-receipt'],
                ['key' => 'diagnosing',    'label' => 'Diagnosa',         'icon' => 'ph ph-magnifying-glass'],
                ['key' => 'in_progress',   'label' => 'Pengerjaan',       'icon' => 'ph ph-hammer'],
                ['key' => 'waiting_parts', 'label' => 'Sparepart',        'icon' => 'ph ph-package'],
                ['key' => 'completed',     'label' => 'Selesai',          'icon' => 'ph ph-check-circle'],
                ['key' => 'picked_up',     'label' => 'Siap / Diambil',   'icon' => 'ph ph-sparkle'],
            ];

            $statusOrder = [
                'received'      => 1,
                'diagnosing'    => 2,
                'in_progress'   => 3,
                'waiting_parts' => 3,
                'completed'     => 5,
                'picked_up'     => 6,
                'cancelled'     => 0,
            ];

            $currentStepIndex = $statusOrder[$so->status] ?? 1;
        @endphp

        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
            <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">Tahapan Progress Service</h3>
            
            <div class="grid grid-cols-3 sm:grid-cols-6 gap-2">
                @foreach ($steps as $idx => $st)
                    @php
                        $stepNumber = $idx + 1;
                        $isCurrent = ($so->status === $st['key']);
                        $isPassed = ($currentStepIndex > $stepNumber);
                    @endphp
                    <div class="flex flex-col items-center text-center p-2.5 rounded-xl border transition-all duration-200 
                        {{ $isCurrent ? 'bg-blue-50 border-blue-300 text-blue-700 font-bold scale-105 shadow-xs' : ($isPassed ? 'bg-emerald-50/70 border-emerald-200 text-emerald-700' : 'bg-slate-50 border-slate-200 text-slate-400') }}">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center mb-1.5 text-lg
                            {{ $isCurrent ? 'bg-blue-600 text-white shadow-sm' : ($isPassed ? 'bg-emerald-500 text-white' : 'bg-slate-200 text-slate-500') }}">
                            <i class="{{ $st['icon'] }}"></i>
                        </div>
                        <span class="text-[10px] leading-tight font-semibold">{{ $st['label'] }}</span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Detail Unit & Keluhan -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-blue-700 flex items-center gap-1.5">
                    <i class="ph ph-guitar text-base"></i> Informasi Unit & Keluhan
                </h4>
                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Nama Unit / Instrument:</span>
                        <span class="font-bold text-slate-900 text-sm">{{ $so->device_name }}</span>
                    </div>
                    @if ($so->serial_number)
                        <div>
                            <span class="text-slate-400 block text-[11px]">Nomor Seri (S/N):</span>
                            <span class="font-mono text-slate-700 font-semibold">{{ $so->serial_number }}</span>
                        </div>
                    @endif
                    <div>
                        <span class="text-slate-400 block text-[11px]">Keluhan / Kendala:</span>
                        <p class="text-slate-700 italic bg-slate-50 p-3 rounded-xl border border-slate-200 mt-1">
                            "{{ $so->complaint ?: 'Tidak ada catatan keluhan khusus.' }}"
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
                <h4 class="text-xs font-bold uppercase tracking-wider text-blue-700 flex items-center gap-1.5">
                    <i class="ph ph-user-gear text-base"></i> Catatan Teknisi & Cabang
                </h4>
                <div class="space-y-2 text-xs">
                    <div>
                        <span class="text-slate-400 block text-[11px]">Teknisi Penanggung Jawab:</span>
                        <span class="font-bold text-slate-900">{{ $so->technician->name ?? 'Tim Teknisi Store' }}</span>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Lokasi Cabang Service:</span>
                        <span class="font-semibold text-slate-800">{{ $so->branch->name }}</span>
                        <p class="text-[11px] text-slate-500 mt-0.5">{{ $so->branch->address }} | Telp: {{ $so->branch->phone }}</p>
                    </div>
                    <div>
                        <span class="text-slate-400 block text-[11px]">Catatan Perbaikan:</span>
                        <p class="text-slate-700 bg-slate-50 p-3 rounded-xl border border-slate-200 mt-1">
                            {{ $so->notes ?: 'Dalam proses pemeriksaan dan penanganan oleh tim teknisi.' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Costs Breakdown -->
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-3">
            <h4 class="text-xs font-bold uppercase tracking-wider text-blue-700 flex items-center gap-1.5">
                <i class="ph ph-receipt text-base"></i> Rincian Biaya & Sparepart
            </h4>

            <div class="space-y-2 text-xs">
                <div class="flex justify-between py-1.5 border-b border-slate-100">
                    <span class="text-slate-600">Estimasi Biaya Jasa Service Awal</span>
                    <span class="font-mono font-semibold text-slate-900">Rp {{ number_format($so->estimated_cost, 0, ',', '.') }}</span>
                </div>

                @if (!empty($so->additional_charges) && is_array($so->additional_charges))
                    <div class="pt-2">
                        <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Sparepart / Layanan Tambahan:</span>
                        @foreach ($so->additional_charges as $chg)
                            <div class="flex justify-between py-1 border-b border-slate-100 text-slate-700">
                                <span>+ {{ $chg['name'] ?? 'Item Tambahan' }}</span>
                                <span class="font-mono">Rp {{ number_format($chg['amount'] ?? 0, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif

                <div class="flex justify-between py-2 border-t border-slate-200 font-bold text-sm text-slate-900 pt-3">
                    <span>Total Estimasi Biaya</span>
                    <span class="font-mono text-base text-emerald-600">Rp {{ number_format($so->total_cost ?: $so->estimated_cost, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <footer class="border-t border-slate-200 bg-white py-6 text-center text-xs text-slate-500">
        <p>&copy; {{ date('Y') }} {{ $so->branch->store_name ?: 'Diego Music Store ERP' }}. All rights reserved.</p>
        <p class="mt-1 text-[11px] text-slate-400">Sistem Informasi Layanan Service & Reparasi Instrument ERP</p>
    </footer>

</body>
</html>
