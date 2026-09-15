<div class="w-full max-w-md">
    <x-pos.toast />

    <!-- Back Link to Portal -->
    <div class="mb-6 flex justify-start">
        <a href="/" class="inline-flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400 hover:text-primary dark:hover:text-blue-400 transition-colors group">
            <i class="ph-bold ph-arrow-left group-hover:-translate-x-1 transition-transform"></i>
            <span>Kembali ke Portal</span>
        </a>
    </div>

    <!-- Main Login Card -->
    <div class="bg-white dark:bg-slate-800 border border-slate-200/80 dark:border-slate-700/80 shadow-2xl rounded-3xl p-8 transition-colors duration-200 relative overflow-hidden">
        <!-- Card Header & Branding -->
        @include('livewire.pos-login.components.card-header')

        @if ($showBranchSelectStep)
            <!-- STEP 2: SELECT BRANCH FORM -->
            @include('livewire.pos-login.components.branch-form')
        @else
            <!-- STEP 1: CREDENTIALS FORM -->
            @include('livewire.pos-login.components.credentials-form')
        @endif
    </div>

    <!-- Theme Switcher -->
    @include('livewire.pos-login.components.theme-toggle')
</div>
