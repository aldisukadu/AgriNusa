<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Detail Peminjaman</h2>
        <div class="mt-3">@include('partials.menu')</div>
    </x-slot>

    @php
        $inputClass = 'block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm';
        $fileClass = 'block mt-1 w-full text-sm text-gray-700 dark:text-gray-300';
    @endphp

    <div class="py-8">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                @include('partials.flash')

                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                    <div><dt class="text-gray-500">Lahan</dt><dd>{{ $peminjaman->lahan->kode }} · {{ $peminjaman->lahan->greenHouse->nama }}</dd></div>
                    <div><dt class="text-gray-500">Periode</dt><dd>{{ $peminjaman->tanggal_mulai->format('d/m/Y') }} – {{ $peminjaman->tanggal_selesai->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-gray-500">Tanaman · Tujuan</dt><dd>{{ $peminjaman->jenis_tanaman }} · {{ $peminjaman->tujuan }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd><x-badge :value="$peminjaman->status" /></dd></div>
                    <div><dt class="text-gray-500">Status deposit</dt><dd><x-badge :value="$peminjaman->status_deposit" /></dd></div>
                    @if ($peminjaman->batas_bayar && $peminjaman->status === 'disetujui')
                        <div><dt class="text-gray-500">Batas pembayaran</dt><dd>{{ $peminjaman->batas_bayar->format('d/m/Y H:i') }}</dd></div>
                    @endif
                    @if ($peminjaman->tanggal_bayar)
                        <div><dt class="text-gray-500">Pembayaran dilaporkan</dt><dd>{{ $peminjaman->metode_bayar }} · {{ $peminjaman->tanggal_bayar->format('d/m/Y H:i') }}</dd></div>
                    @endif
                    @if ($peminjaman->bukti_bayar)
                        <div><dt class="text-gray-500">Bukti bayar</dt><dd><a href="{{ route('bukti-bayar', $peminjaman) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Lihat</a></dd></div>
                    @endif
                    @if ($peminjaman->tanggal_kembali)
                        <div><dt class="text-gray-500">Dikembalikan</dt><dd>{{ $peminjaman->tanggal_kembali->format('d/m/Y') }}</dd></div>
                        <div class="sm:col-span-2"><dt class="text-gray-500">Kondisi saat dikembalikan</dt><dd>{{ $peminjaman->kondisi_kembali }}</dd></div>
                    @endif
                    @if ($peminjaman->foto_kembali)
                        <div><dt class="text-gray-500">Foto kondisi lahan</dt><dd><a href="{{ asset('storage/'.$peminjaman->foto_kembali) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Lihat</a></dd></div>
                    @endif
                    @if ($peminjaman->catatan_admin)
                        <div class="sm:col-span-2"><dt class="text-gray-500">Catatan admin</dt><dd>{{ $peminjaman->catatan_admin }}</dd></div>
                    @endif
                </dl>

                @if ($peminjaman->status === 'disetujui' && $peminjaman->status_deposit === 'belum_dibayar')
                    @if ($peminjaman->tanggal_bayar)
                        <div class="mt-6 p-3 rounded bg-blue-100 text-blue-800 text-sm">Pembayaran sudah dilaporkan. Menunggu konfirmasi admin.</div>
                    @elseif ($peminjaman->batas_bayar && now()->gt($peminjaman->batas_bayar))
                        <div class="mt-6 p-3 rounded bg-red-100 text-red-800 text-sm">Batas pembayaran sudah lewat. Pengajuan akan dibatalkan otomatis.</div>
                    @else
                        <form method="POST" action="{{ route('peminjam.peminjamans.bayar', $peminjaman) }}" enctype="multipart/form-data" class="mt-6">
                            @csrf
                            <h3 class="font-semibold">Bayar deposit Rp {{ number_format($peminjaman->nominal_deposit, 0, ',', '.') }}</h3>
                            <div class="mt-3">
                                <x-input-label for="metode_bayar" value="Metode" />
                                <select id="metode_bayar" name="metode_bayar" required class="{{ $inputClass }}">
                                    @foreach (['transfer', 'tunai'] as $m)
                                        <option value="{{ $m }}" @selected(old('metode_bayar') === $m)>{{ ucfirst($m) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mt-3">
                                <x-input-label for="bukti_bayar" value="Bukti bayar (wajib untuk transfer; JPG, PNG, atau PDF, maks 4 MB)" />
                                <input id="bukti_bayar" name="bukti_bayar" type="file" accept=".jpg,.jpeg,.png,.pdf" class="{{ $fileClass }}">
                            </div>
                            <x-primary-button class="mt-4">Laporkan pembayaran</x-primary-button>
                        </form>
                    @endif
                @endif

                @if ($peminjaman->status === 'menunggu_pemeriksaan')
                    <div class="mt-6 p-3 rounded bg-blue-100 text-blue-800 text-sm">
                        Pengembalian sudah dicatat. Admin akan memeriksa lahan dan menyelesaikan deposit Anda.
                    </div>
                @endif

                @if ($peminjaman->status_deposit !== 'belum_dibayar')
                    @include('partials.rincian-deposit')
                @endif

                <div class="mt-6">
                    <a href="{{ route('peminjam.peminjamans.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">Kembali</a>
                </div>
            </div>

            @if ($peminjaman->status === 'aktif')
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="font-semibold">Kembalikan lahan</h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Bersihkan lahan lebih dulu (sisa tanaman, media, dan peralatan), lalu foto kondisinya. Jika lahan tidak bersih atau tidak dikembalikan sampai {{ config('greenhouse.batas_kembali_hari') }} hari setelah tanggal selesai, admin yang membersihkannya dan biayanya dipotong dari deposit.
                    </p>
                    <form method="POST" action="{{ route('peminjam.peminjamans.kembali', $peminjaman) }}" enctype="multipart/form-data" class="mt-3" onsubmit="return confirm('Kembalikan lahan sekarang?')">
                        @csrf
                        <div>
                            <x-input-label for="kondisi_kembali" value="Kondisi lahan" />
                            <textarea id="kondisi_kembali" name="kondisi_kembali" rows="2" required class="{{ $inputClass }}">{{ old('kondisi_kembali') }}</textarea>
                        </div>
                        <div class="mt-3">
                            <x-input-label for="foto_kembali" value="Foto kondisi lahan (wajib; JPG, PNG, atau WEBP, maks 4 MB)" />
                            <input id="foto_kembali" name="foto_kembali" type="file" accept=".jpg,.jpeg,.png,.webp" required class="{{ $fileClass }}">
                        </div>
                        <x-primary-button class="mt-4">Kembalikan lahan</x-primary-button>
                    </form>
                </div>

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="font-semibold">Catat perawatan tanaman (opsional)</h3>
                    <form method="POST" action="{{ route('peminjam.peminjamans.perawatan.store', $peminjaman) }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <x-input-label for="tanggal" value="Tanggal kegiatan" />
                                <x-text-input id="tanggal" name="tanggal" type="date" class="block mt-1 w-full"
                                    :value="old('tanggal', today()->toDateString())"
                                    min="{{ today()->subDay()->toDateString() }}" max="{{ today()->toDateString() }}" required />
                            </div>
                            <div>
                                <x-input-label for="kegiatan" value="Kegiatan" />
                                <select id="kegiatan" name="kegiatan" required class="{{ $inputClass }}">
                                    @foreach (['menyiram', 'pemupukan', 'penyiangan', 'pengendalian_hama', 'lainnya'] as $k)
                                        <option value="{{ $k }}" @selected(old('kegiatan') === $k)>{{ ucfirst(str_replace('_', ' ', $k)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="catatan" value="Catatan (opsional)" />
                            <textarea id="catatan" name="catatan" rows="2" class="{{ $inputClass }}">{{ old('catatan') }}</textarea>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="foto" value="Foto (opsional; JPG, PNG, atau WEBP, maks 4 MB)" />
                            <input id="foto" name="foto" type="file" accept=".jpg,.jpeg,.png,.webp" class="{{ $fileClass }}">
                        </div>
                        <x-primary-button class="mt-4">Simpan catatan</x-primary-button>
                    </form>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                @include('partials.riwayat-perawatan', ['admin' => false])
            </div>
        </div>
    </div>
</x-app-layout>
