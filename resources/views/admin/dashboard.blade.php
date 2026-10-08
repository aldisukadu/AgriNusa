<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard Admin</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.flash')

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach ([
                    ['Pengajuan menunggu', $jumlah['pengajuan'], route('admin.peminjamans.index', ['status' => 'menunggu'])],
                    ['Laporan bayar belum dikonfirmasi', $jumlah['laporanBayar'], route('admin.peminjamans.index', ['status' => 'disetujui'])],
                    ['Menunggu pemeriksaan', $jumlah['pemeriksaan'], route('admin.peminjamans.index', ['status' => 'menunggu_pemeriksaan'])],
                    ['Peminjaman aktif', $jumlah['aktif'], route('admin.peminjamans.index', ['status' => 'aktif'])],
                ] as [$label, $angka, $tautan])
                    <a href="{{ $tautan }}" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 hover:ring-2 hover:ring-indigo-400">
                        <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">{{ $label }}</div>
                        <div class="text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $angka }}</div>
                    </a>
                @endforeach
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                @foreach ([
                    ['Pengajuan baru', $pengajuan, fn ($p) => $p->tanggal_mulai->format('d/m/Y').' – '.$p->tanggal_selesai->format('d/m/Y')],
                    ['Laporan bayar menunggu konfirmasi', $laporanBayar, fn ($p) => 'Dilaporkan '.$p->tanggal_bayar->format('d/m/Y H:i')],
                    ['Menunggu pemeriksaan', $pemeriksaan, fn ($p) => 'Dikembalikan '.$p->tanggal_kembali->format('d/m/Y')],
                ] as [$judul, $daftar, $keterangan])
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 text-gray-900 dark:text-gray-100">
                        <h3 class="font-semibold">{{ $judul }}</h3>
                        @forelse ($daftar as $p)
                            <a href="{{ route('admin.peminjamans.show', $p) }}" class="block py-2 mt-2 border-t border-gray-200 dark:border-gray-700 text-sm hover:underline">
                                <span class="font-medium">{{ $p->lahan->kode }}</span> · {{ $p->user->name }}<br>
                                <span class="text-gray-500 dark:text-gray-400">{{ $keterangan($p) }}</span>
                            </a>
                        @empty
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada.</p>
                        @endforelse
                    </div>
                @endforeach
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                <h3 class="font-semibold">Ubah cepat lahan</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Status baru hanya berlaku untuk pengajuan berikutnya. Perubahan deposit tidak mengubah peminjaman yang sudah ada.
                    Jika ingin mengubah kode, luas, atau media tanam, gunakan menu Lahan.
                </p>
                <div class="mt-3 overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr><th class="px-3 py-2">Kode</th><th class="px-3 py-2">Status · Deposit</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($lahans as $l)
                                <tr class="border-t border-gray-200 dark:border-gray-700 align-top">
                                    <td class="px-3 py-3 whitespace-nowrap">
                                        <div class="font-medium">{{ $l->kode }}</div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $l->greenHouse->nama }}</div>
                                    </td>
                                    <td class="px-3 py-3">
                                        <form method="POST" action="{{ route('admin.lahans.cepat', $l) }}" class="flex flex-col sm:flex-row gap-2 sm:items-center">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm">
                                                @foreach (['tersedia', 'perawatan'] as $s)
                                                    <option value="{{ $s }}" @selected($l->status === $s)>{{ ucfirst($s) }}</option>
                                                @endforeach
                                            </select>
                                            <input type="number" name="deposit" min="0" step="1000" value="{{ $l->deposit }}"
                                                class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm w-full sm:w-40">
                                            <button class="px-3 py-2 bg-gray-800 dark:bg-gray-200 rounded-md text-xs font-semibold text-white dark:text-gray-800 uppercase tracking-widest whitespace-nowrap">Simpan</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="px-3 py-4 text-gray-500">Belum ada lahan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
