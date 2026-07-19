@props([
    'colspan' => 1,
    'icon' => 'ph-magnifying-glass',
    'message' => 'Tidak ada data ditemukan'
])

<tr>
    <td colspan="{{ $colspan }}" class="px-6 py-12 text-center">
        <div class="flex flex-col items-center gap-2 text-slate-450 dark:text-slate-500">
            <i class="ph {{ $icon }} text-4xl"></i>
            <span class="font-medium text-sm">{{ $message }}</span>
        </div>
    </td>
</tr>
