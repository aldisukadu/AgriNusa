<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Peminjaman</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                <form method="GET" class="mb-4 flex items-center gap-2 text-sm">
                    <label for="status">Status</label>
                    <select id="status" name="status" onchange="this.form.submit()" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md text-sm">
                        <option value="">Semua</option>
                        @foreach ($statuses as $item)
                            <option value="{{ $item }}" @selected($status === $item)>{{ str_replace('_', ' ', $item) }}</option>
                        @endforeach
                    </select>
                </form>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-2">#</th><th class="px-3 py-2">Peminjam</th><th class="px-3 py-2">Lahan</th>
                                <th class="px-3 py-2">Periode</th><th class="px-3 py-2">Status</th><th class="px-3 py-2">Deposit</th><th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($peminjamans as $peminjaman)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-3 py-2">{{ $peminjaman->id }}</td>
                                    <td class="px-3 py-2">{{ $peminjaman->user->name }}</td>
                                    <td class="px-3 py-2">{{ $peminjaman->lahan->kode }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $peminjaman->tanggal_mulai->format('d/m/Y') }} – {{ $peminjaman->tanggal_selesai->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2"><x-badge :value="$peminjaman->status" /></td>
                                    <td class="px-3 py-2"><x-badge :value="$peminjaman->status_deposit" /></td>
                                    <td class="px-3 py-2"><a href="{{ route('pekerja.peminjamans.show', $peminjaman) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Detail</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-3 py-4 text-gray-500">Tidak ada data.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $peminjamans->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
