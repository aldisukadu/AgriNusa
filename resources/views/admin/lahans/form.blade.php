<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $lahan->exists ? 'Edit' : 'Tambah' }} Lahan</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    @php
        $selectClass = 'block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
    @endphp

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $lahan->exists ? route('admin.lahans.update', $lahan) : route('admin.lahans.store') }}">
                    @csrf
                    @if ($lahan->exists) @method('PUT') @endif

                    <div>
                        <x-input-label for="green_house_id" value="Green house" />
                        <select id="green_house_id" name="green_house_id" required class="{{ $selectClass }}">
                            <option value="">Pilih green house</option>
                            @foreach ($greenHouses as $gh)
                                <option value="{{ $gh->id }}" @selected((int) old('green_house_id', $lahan->green_house_id) === $gh->id)>{{ $gh->nama }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('green_house_id')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="kode" value="Kode (unik)" />
                        <x-text-input id="kode" name="kode" class="block mt-1 w-full" :value="old('kode', $lahan->kode)" required />
                        <x-input-error :messages="$errors->get('kode')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="luas" value="Luas (m²)" />
                        <x-text-input id="luas" name="luas" type="number" step="0.01" class="block mt-1 w-full" :value="old('luas', $lahan->luas)" required />
                        <x-input-error :messages="$errors->get('luas')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="media_tanam" value="Media tanam" />
                        <x-text-input id="media_tanam" name="media_tanam" class="block mt-1 w-full" :value="old('media_tanam', $lahan->media_tanam)" required />
                        <x-input-error :messages="$errors->get('media_tanam')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="deposit" value="Deposit (Rp)" />
                        <x-text-input id="deposit" name="deposit" type="number" min="0" step="1000" class="block mt-1 w-full" :value="old('deposit', $lahan->deposit)" required />
                        <x-input-error :messages="$errors->get('deposit')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="status" value="Status" />
                        <select id="status" name="status" required class="{{ $selectClass }}">
                            @foreach (['tersedia', 'perawatan'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $lahan->status) === $s)>{{ ucfirst($s) }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('admin.lahans.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">Batal</a>
                        <x-primary-button class="ms-4">Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
