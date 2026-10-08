@props(['value'])
@php
    $warna = match ($value) {
        'aktif', 'tersedia', 'dibayar', 'dikembalikan' => 'bg-green-100 text-green-800',
        'menunggu', 'menunggu_pemeriksaan', 'belum_dibayar', 'dipotong' => 'bg-yellow-100 text-yellow-800',
        'disetujui', 'perawatan' => 'bg-blue-100 text-blue-800',
        'ditolak', 'dibatalkan', 'terpakai_habis' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp
<span class="px-2 py-0.5 rounded text-xs font-medium whitespace-nowrap {{ $warna }}">{{ str_replace('_', ' ', $value) }}</span>
