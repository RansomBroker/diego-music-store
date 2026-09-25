<!-- Accent Glow -->
<div class="absolute -right-16 -top-16 w-32 h-32 rounded-full bg-primary/5"></div>

<!-- Header -->
<div class="text-center mb-8">
    <img src="{{ asset('images/logo.png') }}" alt="Diego Music Logo" class="w-20 h-20 object-contain mx-auto mb-4">
    <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white tracking-tight">Diego Music POS</h2>
    <p class="text-slate-500 dark:text-slate-400 text-sm mt-1">
        {{ $showBranchSelectStep ? 'Pilih lokasi cabang bertugas Anda' : 'Masuk ke sistem kasir penjualan ritel' }}
    </p>
</div>
