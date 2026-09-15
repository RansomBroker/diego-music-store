<div class="flex h-screen w-full overflow-hidden bg-slate-50 dark:bg-slate-900 transition-colors duration-200">
    <!-- Toast Notification Listener -->
    <x-pos.toast />

    <!-- Leaflet JS CDN Assets -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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

                <!-- Page Header & Action -->
                @include('livewire.pos-store-profile.components.header-bar')

                <!-- Main Layout Grid (Branch Selector & Form & Card Preview) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Form Profil Toko -->
                    @include('livewire.pos-store-profile.components.profile-form')

                    <!-- Live Store Badge Card Preview -->
                    @include('livewire.pos-store-profile.components.store-badge-card')
                </div>

            </div>
        </div>
    </main>

    <!-- Modal Create Store / Cabang -->
    @include('livewire.pos-store-profile.components.create-store-modal')
</div>
