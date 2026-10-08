<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard Peminjam</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @include('partials.flash')

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Menunggu persetujuan</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $jumlahMenunggu }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Peminjaman aktif</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $aktif->count() }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6">
                    <div class="text-xs sm:text-sm text-gray-500 dark:text-gray-400">Menunggu pemeriksaan</div>
                    <div class="text-2xl sm:text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $jumlahPemeriksaan }}</div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-4 sm:p-6 text-gray-900 dark:text-gray-100">
                <h3 class="font-semibold">Perlu tindakan</h3>
                @if ($menungguBayar->isEmpty() && $aktif->isEmpty())
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Tidak ada tindakan yang perlu dilakukan.</p>
                @endif
                <div class="mt-2 space-y-2 text-sm">
                    @foreach ($menungguBayar as $p)
                        <div>
                            <a href="{{ route('peminjam.peminjamans.show', $p) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Bayar deposit lahan {{ $p->lahan->kode }}</a>
                            sebelum {{ $p->batas_bayar->format('d/m/Y H:i') }}, atau pengajuan dibatalkan otomatis.
                        </div>
                    @endforeach
                    @foreach ($aktif as $p)
                        <div>
                            <a href="{{ route('peminjam.peminjamans.show', $p) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Kembalikan lahan {{ $p->lahan->kode }}</a>
                            dalam keadaan bersih paling lambat {{ $p->tanggal_selesai->copy()->addDays($batasKembali)->format('d/m/Y') }}, dengan foto kondisi.
                        </div>
                    @endforeach
                </div>
            </div>

            <div>
                <a href="{{ route('peminjam.lahans.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">Lihat daftar lahan</a>
            </div>
        </div>
    </div>
</x-app-layout>
