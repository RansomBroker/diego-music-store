<x-filament-panels::page>
    <form wire:submit.prevent="save" class="space-y-6">
        {{ $this->form }}

        @if (\App\Helpers\PermissionHelper::canAccessBackofficeFeature(auth()->user(), 'bo.po_smart_assist.edit'))
        <div class="flex justify-end">
            <x-filament::button type="submit" size="lg" color="primary">
                Simpan Pengaturan
            </x-filament::button>
        </div>
        @endif
    </form>
</x-filament-panels::page>
