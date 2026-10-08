<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard Admin</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @include('partials.flash')
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('admin.peminjamans.index', ['status' => 'menunggu']) }}" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-400">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Pengajuan menunggu</div>
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $menunggu }}</div>
                </a>
                <a href="{{ route('admin.peminjamans.index', ['status' => 'menunggu_pemeriksaan']) }}" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-400">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Menunggu pemeriksaan</div>
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $pemeriksaan }}</div>
                </a>
                <a href="{{ route('admin.peminjamans.index', ['status' => 'aktif']) }}" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-400">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Peminjaman aktif</div>
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $aktif }}</div>
                </a>
                <a href="{{ route('admin.lahans.index') }}" class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 hover:ring-2 hover:ring-indigo-400">
                    <div class="text-sm text-gray-500 dark:text-gray-400">Lahan tersedia</div>
                    <div class="text-3xl font-semibold text-gray-900 dark:text-gray-100">{{ $lahanTersedia }} <span class="text-base text-gray-500">/ {{ $lahanTotal }}</span></div>
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
