<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Dashboard Admin</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                Sistem Peminjaman Lahan Green House {{ config('app.instansi', config('app.name')) }}.
                Login sebagai {{ auth()->user()->name }} (admin).
            </div>
        </div>
    </div>
</x-app-layout>
