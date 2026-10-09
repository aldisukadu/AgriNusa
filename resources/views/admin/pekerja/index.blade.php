<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Akun Pekerja</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                @include('partials.flash')

                <div class="mb-4">
                    <a href="{{ route('admin.pekerja.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">Buat akun pekerja</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr><th class="px-3 py-2">Nama</th><th class="px-3 py-2">Email</th><th class="px-3 py-2">Nomor telepon</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($pekerjas as $pekerja)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-3 py-2">{{ $pekerja->name }}</td>
                                    <td class="px-3 py-2">{{ $pekerja->email }}</td>
                                    <td class="px-3 py-2">{{ $pekerja->no_hp ?: '-' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-3 py-4 text-gray-500">Belum ada akun pekerja.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $pekerjas->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
