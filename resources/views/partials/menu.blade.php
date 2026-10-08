@php
    $menu = auth()->user()->isAdmin()
        ? [
            ['Dashboard', 'admin.dashboard', 'admin.dashboard'],
            ['Green House', 'admin.green-houses.index', 'admin.green-houses.*'],
            ['Lahan', 'admin.lahans.index', 'admin.lahans.*'],
            ['Peminjaman', 'admin.peminjamans.index', 'admin.peminjamans.*'],
        ]
        : [
            ['Dashboard', 'peminjam.dashboard', 'peminjam.dashboard'],
            ['Daftar Lahan', 'peminjam.lahans.index', 'peminjam.lahans.*'],
            ['Peminjaman Saya', 'peminjam.peminjamans.index', 'peminjam.peminjamans.*'],
        ];
@endphp
<nav class="flex flex-wrap gap-2 text-sm">
    @foreach ($menu as [$label, $rute, $pola])
        <a href="{{ route($rute) }}"
           class="px-3 py-1 rounded-md {{ request()->routeIs($pola) ? 'bg-indigo-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-200 hover:bg-gray-200 dark:hover:bg-gray-600' }}">
            {{ $label }}
        </a>
    @endforeach
</nav>
