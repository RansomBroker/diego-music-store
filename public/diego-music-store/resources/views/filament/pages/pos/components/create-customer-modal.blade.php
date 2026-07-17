@props([
    'showCreateCustomerModal',
    'newCustomerName',
    'newCustomerPhone',
    'newCustomerEmail',
    'newCustomerAddress',
    'newCustomerPricingTierId',
    'newCustomerIsLoyaltyMember',
    'pricingTiers' => []
])

<x-pos-page::modal 
    :show="$showCreateCustomerModal" 
    title="Daftarkan Pelanggan Baru" 
    closeAction="$set('showCreateCustomerModal', false)"
    maxWidth="md"
>
    <form wire:submit.prevent="createCustomer" class="space-y-4">
        <!-- Nama Lengkap -->
        <x-pos.form.input 
            label="Nama Lengkap" 
            model="newCustomerName" 
            placeholder="Contoh: Budi Santoso" 
            icon="ph-user" 
            required 
        />

        <!-- Nomor Telepon -->
        <x-pos.form.input 
            label="Nomor Telepon" 
            model="newCustomerPhone" 
            placeholder="Contoh: 08123456789" 
            icon="ph-phone" 
        />

        <!-- Email -->
        <x-pos.form.input 
            label="Email" 
            type="email"
            model="newCustomerEmail" 
            placeholder="Contoh: budi@gmail.com" 
            icon="ph-envelope" 
        />

        <!-- Alamat -->
        <x-pos.form.input 
            label="Alamat" 
            model="newCustomerAddress" 
            placeholder="Contoh: Jl. Sudirman No. 123, Jakarta" 
            icon="ph-map-pin" 
        />

        <!-- Tingkat Harga Default -->
        <x-pos.form.select 
            label="Tingkat Harga Default" 
            model="newCustomerPricingTierId" 
            icon="ph-tag"
        >
            @foreach ($pricingTiers as $tier)
                <option value="{{ $tier->id }}" class="bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100">{{ $tier->name }}</option>
            @endforeach
        </x-pos.form.select>

        <!-- Action Button -->
        <x-pos.utility.button 
            type="submit" 
            variant="primary" 
            size="lg" 
            icon="ph-floppy-disk"
            loading="createCustomer"
            class="mt-6"
        >
            Simpan Pelanggan
        </x-pos.utility.button>
    </form>
</x-pos-page::modal>
