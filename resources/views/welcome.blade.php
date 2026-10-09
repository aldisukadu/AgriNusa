<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Informasi peminjaman lahan green house AgriNusa, mulai dari pengajuan sampai pengembalian deposit.">
        <title>AgriNusa — Peminjaman Lahan Green House</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-gray-100 text-gray-900 antialiased dark:bg-gray-950 dark:text-gray-100">
        <div class="min-h-screen">
            <header class="border-b border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900">
                <nav class="mx-auto flex max-w-7xl items-center justify-between px-5 py-4 sm:px-8 lg:px-12" aria-label="Navigasi utama">
                    <a href="/" class="rounded-sm focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-gray-900" aria-label="AgriNusa, halaman utama">
                        <x-application-logo />
                    </a>

                    <div class="flex items-center gap-3 sm:gap-5">
                        @auth
                            <a href="{{ route('dashboard') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-md px-3 py-2 text-sm font-medium text-gray-700 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">
                                Masuk
                            </a>
                            <a href="{{ route('register') }}" class="rounded-md border border-indigo-600 px-4 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-600 hover:text-white focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:border-indigo-400 dark:text-indigo-400 dark:hover:bg-indigo-400 dark:hover:text-gray-950 dark:focus:ring-offset-gray-900">
                                Daftar
                            </a>
                        @endauth
                    </div>
                </nav>
            </header>

            <main>
                <section class="mx-auto grid max-w-7xl gap-12 px-5 py-16 sm:px-8 sm:py-20 md:grid-cols-12 md:items-center lg:px-12 lg:py-28" aria-labelledby="hero-title">
                    <div class="md:col-span-7">
                        <p class="mb-5 text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600 dark:text-indigo-400">Peminjaman lahan green house</p>
                        <h1 id="hero-title" class="max-w-3xl text-4xl font-semibold leading-tight tracking-tight text-gray-900 sm:text-5xl lg:text-6xl dark:text-gray-100">
                            Ajukan lahan, kelola peminjaman dengan jelas.
                        </h1>
                        <p class="mt-6 max-w-2xl text-base leading-7 text-gray-600 sm:text-lg sm:leading-8 dark:text-gray-300">
                            AgriNusa membantu peminjam mengajukan lahan, menyelesaikan deposit, menggunakan lahan, dan mengembalikannya. Admin mengelola ketersediaan lahan serta persetujuan dalam satu alur.
                        </p>
                        <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                            @auth
                                <a href="{{ route('dashboard') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-gray-950">
                                    Buka dashboard
                                </a>
                            @else
                                <a href="{{ route('register') }}" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-5 py-3 text-sm font-semibold text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:focus:ring-offset-gray-950">
                                    Daftar sebagai peminjam
                                </a>
                                <a href="{{ route('login') }}" class="inline-flex items-center justify-center rounded-md border border-gray-300 bg-white px-5 py-3 text-sm font-semibold text-gray-800 hover:border-indigo-600 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:border-indigo-400 dark:hover:text-indigo-400 dark:focus:ring-offset-gray-950">
                                    Masuk
                                </a>
                            @endauth
                        </div>
                    </div>

                    <div class="md:col-span-5">
                        <div class="rounded-2xl border border-gray-200 bg-white p-6 sm:p-8 dark:border-gray-800 dark:bg-gray-900">
                            <div class="flex items-center gap-4">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-gray-100 text-indigo-600 dark:bg-gray-800 dark:text-indigo-400" aria-hidden="true">
                                    <svg viewBox="0 0 32 32" class="h-7 w-7 fill-current" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M25.5 4.5c-7.9.2-14 2.4-17.5 6.7-2.8 3.4-2.6 7.8.4 10.5 2.7 2.4 6.8 2.3 9.7-.3 4-3.6 6.1-9.2 7.4-16.9ZM7.1 24.1c4.4-5.2 8.8-8.6 14.8-12.2-4.1 3.2-7.8 6.8-11.4 11.4l-1.1 4.2h-3l.7-3.4Z"/>
                                    </svg>
                                </span>
                                <div>
                                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Satu alur peminjaman</p>
                                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">Dari pengajuan sampai pengembalian</p>
                                </div>
                            </div>
                            <div class="mt-6 border-t border-gray-200 pt-5 dark:border-gray-800">
                                <p class="text-sm leading-6 text-gray-600 dark:text-gray-300">
                                    Deposit digunakan untuk biaya pembersihan setelah lahan dikembalikan. Sisa deposit diselesaikan kembali kepada peminjam.
                                </p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="border-y border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900" aria-labelledby="steps-title">
                    <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20 lg:px-12">
                        <div class="max-w-2xl">
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600 dark:text-indigo-400">Alur peminjaman</p>
                            <h2 id="steps-title" class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-gray-100">Empat langkah yang mudah diikuti</h2>
                            <p class="mt-4 leading-7 text-gray-600 dark:text-gray-300">Setiap tahap tercatat agar peminjam dan admin mengetahui hal yang perlu dilakukan berikutnya.</p>
                        </div>

                        <ol class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                            <li class="rounded-xl border border-gray-200 bg-gray-100 p-5 dark:border-gray-800 dark:bg-gray-950">
                                <span class="text-sm font-semibold tracking-wide text-indigo-600 dark:text-indigo-400">01</span>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Ajukan lahan</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Pilih lahan yang tersedia dan kirim pengajuan peminjaman.</p>
                            </li>
                            <li class="rounded-xl border border-gray-200 bg-gray-100 p-5 dark:border-gray-800 dark:bg-gray-950">
                                <span class="text-sm font-semibold tracking-wide text-indigo-600 dark:text-indigo-400">02</span>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Bayar deposit</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Setelah disetujui, laporkan pembayaran deposit sesuai batas waktu.</p>
                            </li>
                            <li class="rounded-xl border border-gray-200 bg-gray-100 p-5 dark:border-gray-800 dark:bg-gray-950">
                                <span class="text-sm font-semibold tracking-wide text-indigo-600 dark:text-indigo-400">03</span>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Gunakan lahan</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Gunakan lahan selama periode peminjaman yang disetujui.</p>
                            </li>
                            <li class="rounded-xl border border-gray-200 bg-gray-100 p-5 dark:border-gray-800 dark:bg-gray-950">
                                <span class="text-sm font-semibold tracking-wide text-indigo-600 dark:text-indigo-400">04</span>
                                <h3 class="mt-4 text-lg font-semibold text-gray-900 dark:text-gray-100">Kembalikan lahan</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Kembalikan lahan; biaya pembersihan dipotong dari deposit dan sisanya diselesaikan.</p>
                            </li>
                        </ol>
                    </div>
                </section>

                <section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20 lg:px-12" aria-labelledby="rules-title">
                    <div class="max-w-2xl">
                        <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600 dark:text-indigo-400">Deposit dan waktu</p>
                        <h2 id="rules-title" class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-gray-100">Perhatikan batas penting ini</h2>
                    </div>

                    <div class="mt-8 grid gap-4 md:grid-cols-3">
                        <article class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Deposit</p>
                            <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">Untuk pembersihan lahan</h3>
                            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">Deposit digunakan untuk biaya pembersihan setelah peminjaman. Sisa dana dikembalikan kepada peminjam setelah penyelesaian.</p>
                        </article>
                        <article class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Setelah pengajuan disetujui</p>
                            <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">Laporkan pembayaran dalam {{ config('greenhouse.batas_bayar_jam') }} jam</h3>
                            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">Jika pembayaran tidak dilaporkan dalam batas tersebut, pengajuan dibatalkan otomatis.</p>
                        </article>
                        <article class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-gray-900">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Setelah tanggal selesai</p>
                            <h3 class="mt-2 text-lg font-semibold text-gray-900 dark:text-gray-100">Kembalikan dalam {{ config('greenhouse.batas_kembali_hari') }} hari</h3>
                            <p class="mt-3 text-sm leading-6 text-gray-600 dark:text-gray-300">Jika melewati batas pengembalian, lahan ditandai untuk diperiksa admin.</p>
                        </article>
                    </div>
                </section>

                <section class="border-y border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900" aria-labelledby="roles-title">
                    <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20 lg:px-12">
                        <div class="max-w-2xl">
                            <p class="text-sm font-semibold uppercase tracking-[0.16em] text-indigo-600 dark:text-indigo-400">Pengguna AgriNusa</p>
                            <h2 id="roles-title" class="mt-3 text-3xl font-semibold tracking-tight text-gray-900 sm:text-4xl dark:text-gray-100">Dibuat untuk dua peran</h2>
                        </div>
                        <div class="mt-8 grid gap-4 md:grid-cols-2">
                            <article class="rounded-xl border border-gray-200 bg-gray-100 p-6 sm:p-8 dark:border-gray-800 dark:bg-gray-950">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Peminjam</h3>
                                <p class="mt-3 leading-7 text-gray-600 dark:text-gray-300">Ajukan lahan, laporkan pembayaran deposit, gunakan lahan selama periode yang disetujui, lalu lakukan pengembalian.</p>
                            </article>
                            <article class="rounded-xl border border-gray-200 bg-gray-100 p-6 sm:p-8 dark:border-gray-800 dark:bg-gray-950">
                                <h3 class="text-xl font-semibold text-gray-900 dark:text-gray-100">Admin</h3>
                                <p class="mt-3 leading-7 text-gray-600 dark:text-gray-300">Kelola green house dan lahan, tinjau persetujuan peminjaman, serta tindak lanjuti pengembalian dan pemeriksaan lahan.</p>
                            </article>
                        </div>
                    </div>
                </section>
            </main>

            <footer class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-8 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12 dark:text-gray-400">
                <p>&copy; {{ date('Y') }} {{ config('app.instansi', env('APP_INSTANSI', config('app.name'))) }}. AgriNusa.</p>
                <a href="{{ route('login') }}" class="w-fit font-medium text-gray-700 hover:text-indigo-600 focus:outline-none focus:ring-2 focus:ring-indigo-600 dark:text-gray-300 dark:hover:text-indigo-400">
                    Masuk
                </a>
            </footer>
        </div>
    </body>
</html>
