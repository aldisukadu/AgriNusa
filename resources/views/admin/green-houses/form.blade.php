<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $greenHouse->exists ? 'Edit' : 'Tambah' }} Green House</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ $greenHouse->exists ? route('admin.green-houses.update', $greenHouse) : route('admin.green-houses.store') }}">
                    @csrf
                    @if ($greenHouse->exists) @method('PUT') @endif

                    <div>
                        <x-input-label for="nama" value="Nama" />
                        <x-text-input id="nama" name="nama" class="block mt-1 w-full" :value="old('nama', $greenHouse->nama)" required autofocus />
                        <x-input-error :messages="$errors->get('nama')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="lokasi" value="Lokasi" />
                        <x-text-input id="lokasi" name="lokasi" class="block mt-1 w-full" :value="old('lokasi', $greenHouse->lokasi)" required />
                        <x-input-error :messages="$errors->get('lokasi')" class="mt-2" />
                    </div>
                    <div class="mt-4">
                        <x-input-label for="keterangan" value="Keterangan" />
                        <textarea id="keterangan" name="keterangan" rows="3" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">{{ old('keterangan', $greenHouse->keterangan) }}</textarea>
                        <x-input-error :messages="$errors->get('keterangan')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <a href="{{ route('admin.green-houses.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">Batal</a>
                        <x-primary-button class="ms-4">Simpan</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
