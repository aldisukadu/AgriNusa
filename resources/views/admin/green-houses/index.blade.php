<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Green House</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                @include('partials.flash')

                <div class="mb-4">
                    <a href="{{ route('admin.green-houses.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">Tambah</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr><th class="px-3 py-2">Nama</th><th class="px-3 py-2">Lokasi</th><th class="px-3 py-2">Lahan</th><th class="px-3 py-2"></th></tr>
                        </thead>
                        <tbody>
                            @forelse ($greenHouses as $gh)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-3 py-2">{{ $gh->nama }}</td>
                                    <td class="px-3 py-2">{{ $gh->lokasi }}</td>
                                    <td class="px-3 py-2">{{ $gh->lahans_count }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">
                                        <a href="{{ route('admin.green-houses.edit', $gh) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Edit</a>
                                        <form method="POST" action="{{ route('admin.green-houses.destroy', $gh) }}" class="inline" onsubmit="return confirm('Hapus green house ini?')">
                                            @csrf @method('DELETE')
                                            <button class="ms-3 text-red-600 hover:underline">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="px-3 py-4 text-gray-500">Belum ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
