<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Daftar Lahan</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                @include('partials.flash')

                <form method="GET" class="mb-6 flex flex-col sm:flex-row sm:items-end gap-3 text-sm">
                    <div>
                        <x-input-label for="mulai" value="Mulai" />
                        <x-text-input id="mulai" name="mulai" type="date" class="block mt-1" :value="$mulai" />
                    </div>
                    <div>
                        <x-input-label for="selesai" value="Selesai" />
                        <x-text-input id="selesai" name="selesai" type="date" class="block mt-1" :value="$selesai" />
                    </div>
                    <x-primary-button>Cek ketersediaan</x-primary-button>
                    @if ($mulai)
                        <a href="{{ route('peminjam.lahans.index') }}" class="underline text-gray-600 dark:text-gray-400">Reset</a>
                    @endif
                </form>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    @foreach ($lahans as $l)
                        @php $bisa = $tersedia === null ? $l->status === 'tersedia' : $tersedia->contains($l->id); @endphp
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 {{ $tersedia !== null && ! $bisa ? 'opacity-60' : '' }}">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <div class="font-semibold">{{ $l->kode }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $l->greenHouse->nama }} · {{ $l->greenHouse->lokasi }}</div>
                                </div>
                                <div class="flex flex-col items-end gap-1">
                                    <x-badge :value="$l->status" />
                                    @if ($tersedia !== null)
                                        <span class="text-xs {{ $bisa ? 'text-green-600' : 'text-red-600' }}">{{ $bisa ? 'Kosong di rentang ini' : 'Tidak tersedia di rentang ini' }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-2 text-sm">{{ $l->luas }} m² · {{ $l->media_tanam }}</div>
                            <div class="text-sm">Deposit: <strong>Rp {{ number_format($l->deposit, 0, ',', '.') }}</strong></div>

                            @if ($l->peminjamans->isNotEmpty())
                                <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                    Terisi:
                                    @foreach ($l->peminjamans as $p)
                                        <span class="inline-block me-2">{{ $p->tanggal_mulai->format('d/m/Y') }}–{{ $p->tanggal_selesai->format('d/m/Y') }}</span>
                                    @endforeach
                                </div>
                            @endif

                            @if ($l->status === 'tersedia')
                                <a href="{{ route('peminjam.peminjamans.create', ['lahan' => $l->id, 'mulai' => $mulai, 'selesai' => $selesai]) }}" class="inline-block mt-3 text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Ajukan peminjaman</a>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
