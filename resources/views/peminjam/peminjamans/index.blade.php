<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Peminjaman Saya</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                @include('partials.flash')

                <div class="mb-4">
                    <a href="{{ route('peminjam.peminjamans.create') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest">Ajukan peminjaman</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase text-gray-500 dark:text-gray-400">
                            <tr>
                                <th class="px-3 py-2">Lahan</th><th class="px-3 py-2">Periode</th><th class="px-3 py-2">Tanaman</th>
                                <th class="px-3 py-2">Status</th><th class="px-3 py-2">Deposit</th><th class="px-3 py-2"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($peminjamans as $p)
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td class="px-3 py-2">{{ $p->lahan->kode }}</td>
                                    <td class="px-3 py-2 whitespace-nowrap">{{ $p->tanggal_mulai->format('d/m/Y') }} – {{ $p->tanggal_selesai->format('d/m/Y') }}</td>
                                    <td class="px-3 py-2">{{ $p->jenis_tanaman }}</td>
                                    <td class="px-3 py-2"><x-badge :value="$p->status" /></td>
                                    <td class="px-3 py-2"><x-badge :value="$p->status_deposit" /></td>
                                    <td class="px-3 py-2"><a href="{{ route('peminjam.peminjamans.show', $p) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Detail</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="px-3 py-4 text-gray-500">Belum ada peminjaman.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-4">{{ $peminjamans->links() }}</div>
            </div>
        </div>
    </div>
</x-app-layout>
