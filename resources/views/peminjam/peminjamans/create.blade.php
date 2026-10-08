<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Ajukan Peminjaman</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    @php
        $selectClass = 'block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    @endphp

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                @include('partials.flash', ['tanpaError' => true])

                <form method="POST" action="{{ route('peminjam.peminjamans.store') }}">
                    @csrf

                    <div>
                        <x-input-label for="lahan_id" value="Lahan" />
                        <select id="lahan_id" name="lahan_id" required class="{{ $selectClass }}">
                            <option value="">Pilih lahan</option>
                            @foreach ($lahans as $l)
                                <option value="{{ $l->id }}" @selected((int) old('lahan_id', $dipilih) === $l->id)>
                                    {{ $l->kode }} · {{ $l->greenHouse->nama }} · deposit Rp {{ number_format($l->deposit, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('lahan_id')" class="mt-2" />
                    </div>

                    <div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="tanggal_mulai" value="Tanggal mulai" />
                            <x-text-input id="tanggal_mulai" name="tanggal_mulai" type="date" class="block mt-1 w-full" :value="old('tanggal_mulai', $mulai)" required />
                            <x-input-error :messages="$errors->get('tanggal_mulai')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="tanggal_selesai" value="Tanggal selesai" />
                            <x-text-input id="tanggal_selesai" name="tanggal_selesai" type="date" class="block mt-1 w-full" :value="old('tanggal_selesai', $selesai)" required />
                            <x-input-error :messages="$errors->get('tanggal_selesai')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-4">
                        <x-input-label for="jenis_tanaman" value="Jenis tanaman" />
                        <x-text-input id="jenis_tanaman" name="jenis_tanaman" class="block mt-1 w-full" :value="old('jenis_tanaman')" required />
                        <x-input-error :messages="$errors->get('jenis_tanaman')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="tujuan" value="Tujuan" />
                        <select id="tujuan" name="tujuan" required class="{{ $selectClass }}">
                            @foreach (['praktikum', 'penelitian', 'budidaya'] as $t)
                                <option value="{{ $t }}" @selected(old('tujuan') === $t)>{{ ucfirst($t) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('tujuan')" class="mt-2" />
                    </div>

                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Lahan harus dikembalikan dalam keadaan bersih saat masa pinjam berakhir, disertai foto kondisi lahan. Jika tidak bersih atau tidak dikembalikan sampai {{ config('greenhouse.batas_kembali_hari') }} hari setelah tanggal selesai, admin yang membersihkannya dan biayanya dipotong dari deposit. Sisa deposit dikembalikan.
                    </p>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('peminjam.lahans.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">Batal</a>
                        <x-primary-button class="ms-4">Kirim pengajuan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
