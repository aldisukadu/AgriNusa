<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard Peminjam</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.flash')
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Peminjaman aktif</div>
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $aktif }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Menunggu persetujuan</div>
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $menunggu }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Disetujui, belum bayar deposit</div>
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $disetujui }}</div>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('peminjam.lahans.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">Lihat daftar lahan</a>
            </div>
        </div>
    </div>
</x-app-layout>
