{{--
    Komponen Table Actions POS — <x-pos.table.actions ...>
    =======================================================
    Props:
      - editAction   : string|null  — ekspresi wire:click untuk tombol Edit (misal: "openEdit(1)")
      - deleteAction : string|null  — ekspresi wire:click untuk tombol Hapus (misal: "confirmDelete(1)")
      - editTitle    : string       — tooltip tombol Edit
      - deleteTitle  : string       — tooltip tombol Hapus
      - showEdit     : bool         — tampilkan tombol Edit (default: true)
      - showDelete   : bool         — tampilkan tombol Hapus (default: true)
--}}
@props([
    'editAction'   => null,
    'deleteAction' => null,
    'editTitle'    => 'Edit',
    'deleteTitle'  => 'Hapus',
    'showEdit'     => true,
    'showDelete'   => true,
])

<div {{ $attributes->merge(['class' => 'flex items-center justify-end gap-1.5']) }}>
    {{ $slot }}

    @if ($showEdit && $editAction)
        <button
            type="button"
            wire:click="{{ $editAction }}"
            class="inline-flex items-center justify-center p-2 rounded-lg bg-amber-500 hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700 text-white shadow-sm shadow-amber-500/20 hover:shadow-md transition-all duration-150 cursor-pointer active:scale-95 group"
            title="{{ $editTitle }}"
        >
            <i class="ph-bold ph-pencil-simple text-sm"></i>
        </button>
    @endif

    @if ($showDelete && $deleteAction)
        <button
            type="button"
            wire:click="{{ $deleteAction }}"
            class="inline-flex items-center justify-center p-2 rounded-lg bg-rose-500 hover:bg-rose-600 dark:bg-rose-600 dark:hover:bg-rose-700 text-white shadow-sm shadow-rose-500/20 hover:shadow-md transition-all duration-150 cursor-pointer active:scale-95 group"
            title="{{ $deleteTitle }}"
        >
            <i class="ph-bold ph-trash text-sm"></i>
        </button>
    @endif
</div>
