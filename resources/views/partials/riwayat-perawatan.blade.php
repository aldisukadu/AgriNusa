@php
    $logs = $peminjaman->perawatanLogs()->with('user')->latest('tanggal')->latest('id')->get();
@endphp
<h3 class="font-semibold">Riwayat perawatan dan pembersihan</h3>
<div class="mt-2 overflow-x-auto">
    <table class="w-full text-sm text-left">
        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
            <tr>
                <th class="px-3 py-2">Tanggal</th><th class="px-3 py-2">Pelaksana</th><th class="px-3 py-2">Kegiatan</th>
                <th class="px-3 py-2">Biaya</th><th class="px-3 py-2">Catatan</th><th class="px-3 py-2">Foto</th><th class="px-3 py-2"></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($logs as $log)
                <tr class="border-t border-gray-200 dark:border-gray-700 align-top">
                    <td class="px-3 py-2 whitespace-nowrap">{{ $log->tanggal->format('d/m/Y') }}</td>
                    <td class="px-3 py-2">{{ $log->pelaksana }} <span class="text-gray-500">({{ $log->user->name }})</span></td>
                    <td class="px-3 py-2">{{ str_replace('_', ' ', $log->kegiatan) }}</td>
                    <td class="px-3 py-2 whitespace-nowrap">{{ $log->biaya > 0 ? 'Rp '.number_format($log->biaya, 0, ',', '.') : '-' }}</td>
                    <td class="px-3 py-2">{{ $log->catatan ?? '-' }}</td>
                    <td class="px-3 py-2">
                        @if ($log->foto)
                            <a href="{{ asset('storage/'.$log->foto) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Lihat</a>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        @if (($admin ?? false) && $log->pelaksana === 'admin' && $peminjaman->status === 'menunggu_pemeriksaan')
                            <form method="POST" action="{{ route('admin.pembersihan.destroy', $log) }}" onsubmit="return confirm('Hapus catatan ini? Total biaya dihitung ulang.')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:underline">Hapus</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-3 py-4 text-gray-500">Belum ada catatan.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
