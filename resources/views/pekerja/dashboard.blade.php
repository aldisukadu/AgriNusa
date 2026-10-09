<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard Pekerja</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.flash')

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Menunggu pemeriksaan</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $jumlahPemeriksaan }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Peminjaman aktif</div>
                    <div class="mt-1 text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $jumlahAktif }}</div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                @foreach ([
                    ['Menunggu pemeriksaan dan pembersihan', $pemeriksaan],
                    ['Pengembalian melewati batas', $pengembalianTerlambat],
                ] as [$judul, $daftar])
                    <section class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                        <h3 class="font-semibold">{{ $judul }}</h3>
                        @forelse ($daftar as $peminjaman)
                            <a href="{{ route('pekerja.peminjamans.show', $peminjaman) }}" class="block py-3 mt-2 border-t border-gray-200 dark:border-gray-700 text-sm hover:underline">
                                <span class="font-medium">{{ $peminjaman->lahan->kode }}</span> · {{ $peminjaman->user->name }}
                                <span class="block text-gray-500 dark:text-gray-400">
                                    {{ $peminjaman->status === 'aktif' ? 'Selesai '.$peminjaman->tanggal_selesai->format('d/m/Y') : 'Dikembalikan '.$peminjaman->tanggal_kembali?->format('d/m/Y') }}
                                </span>
                            </a>
                        @empty
                            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada peminjaman.</p>
                        @endforelse
                    </section>
                @endforeach
            </div>

            <a href="{{ route('pekerja.peminjamans.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">
                Lihat semua peminjaman
            </a>
        </div>
    </div>
</x-app-layout>
