{{--
    Header POS Kasir
    Wrapper tipis di atas x-pos.navbar khusus untuk halaman /pos (kasir penuh).
    Menambahkan props spesifik kasir: branches, todaySalesTotal, dan tombol Tutup Sesi.
--}}
@props([
    'branches',
    'selectedBranchId',
    'selectedStoreName',
    'selectedBranchName',
    'activeSessionInfo' => null,
    'todaySalesTotal'   => 0,
])

<x-pos.navbar
    pageTitle="Point of Sale"
    :showBack="true"
    backLabel="Dashboard"
    :activeSessionInfo="$activeSessionInfo"
    :todaySalesTotal="$todaySalesTotal"
    :branches="$branches"
    :selectedBranchId="$selectedBranchId"
    :showBranchSelector="true"
    :showCloseSession="!empty($activeSessionInfo)"
/>
