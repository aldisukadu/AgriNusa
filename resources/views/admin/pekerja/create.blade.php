<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Buat Akun Pekerja</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                <form method="POST" action="{{ route('admin.pekerja.store') }}" class="space-y-4">
                    @csrf

                    <div>
                        <x-input-label for="name" value="Nama pekerja" />
                        <x-text-input id="name" name="name" type="text" class="block mt-1 w-full" :value="old('name')" required autofocus autocomplete="name" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="email" value="Email" />
                        <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" required autocomplete="email" />
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="no_hp" value="Nomor telepon (opsional)" />
                        <x-text-input id="no_hp" name="no_hp" type="tel" class="block mt-1 w-full" :value="old('no_hp')" autocomplete="tel" />
                        <x-input-error :messages="$errors->get('no_hp')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password" value="Kata sandi awal" />
                        <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="password_confirmation" value="Ulangi kata sandi" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                    </div>

                    <div class="flex items-center gap-4 pt-2">
                        <x-primary-button>Buat akun</x-primary-button>
                        <a href="{{ route('admin.pekerja.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
