<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">Peminjaman #{{ $peminjaman->id }}</h2>
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
                    <div><dt class="text-gray-500">Peminjam</dt><dd>{{ $peminjaman->user->name }} ({{ $peminjaman->user->status_pengguna }}) · {{ $peminjaman->user->no_hp }}</dd></div>
                    <div><dt class="text-gray-500">Lahan</dt><dd>{{ $peminjaman->lahan->kode }} · {{ $peminjaman->lahan->greenHouse->nama }}</dd></div>
                    <div><dt class="text-gray-500">Periode</dt><dd>{{ $peminjaman->tanggal_mulai->format('d/m/Y') }} – {{ $peminjaman->tanggal_selesai->format('d/m/Y') }}</dd></div>
                    <div><dt class="text-gray-500">Tanaman · Tujuan</dt><dd>{{ $peminjaman->jenis_tanaman }} · {{ $peminjaman->tujuan }}</dd></div>
                    <div><dt class="text-gray-500">Status</dt><dd><x-badge :value="$peminjaman->status" /></dd></div>
                    <div><dt class="text-gray-500">Status deposit</dt><dd><x-badge :value="$peminjaman->status_deposit" /></dd></div>
                    @if ($peminjaman->aktif_sejak)
                        <div><dt class="text-gray-500">Status perawatan</dt><dd><x-badge :value="$peminjaman->status_perawatan" /></dd></div>
                        <div><dt class="text-gray-500">Aktif sejak</dt><dd>{{ $peminjaman->aktif_sejak->format('d/m/Y H:i') }}</dd></div>
                    @endif
                    @if ($peminjaman->batas_bayar)
                        <div><dt class="text-gray-500">Batas bayar</dt><dd>{{ $peminjaman->batas_bayar->format('d/m/Y H:i') }}</dd></div>
                    @endif
                    @if ($peminjaman->tanggal_bayar)
                        <div><dt class="text-gray-500">Pembayaran dilaporkan</dt><dd>{{ $peminjaman->metode_bayar }} · {{ $peminjaman->tanggal_bayar->format('d/m/Y H:i') }}</dd></div>
                    @endif
                    @if ($peminjaman->bukti_bayar)
                        <div><dt class="text-gray-500">Bukti bayar</dt><dd><a href="{{ route('bukti-bayar', $peminjaman) }}" target="_blank" class="text-indigo-600 dark:text-indigo-400 hover:underline">Lihat</a></dd></div>
                    @endif
                    @if ($peminjaman->catatan_admin)
                        <div class="sm:col-span-2"><dt class="text-gray-500">Catatan admin</dt><dd>{{ $peminjaman->catatan_admin }}</dd></div>
                    @endif
                </dl>

                @if ($peminjaman->status === 'menunggu')
                    @if ($bersaing > 0)
                        <div class="mt-6 p-3 rounded bg-yellow-100 text-yellow-800 text-sm">
                            {{ $bersaing }} pengajuan lain masih menunggu untuk lahan dan periode yang beririsan. Menyetujui pengajuan ini tidak menolak yang lain otomatis; pengajuan lain itu akan gagal disetujui karena bentrok.
                        </div>
                    @endif

                    <div class="mt-6 flex flex-col sm:flex-row gap-6">
                        <form method="POST" action="{{ route('admin.peminjamans.setujui', $peminjaman) }}">
                            @csrf
                            <x-primary-button>Setujui</x-primary-button>
                        </form>

                        <form method="POST" action="{{ route('admin.peminjamans.tolak', $peminjaman) }}" class="flex-1">
                            @csrf
                            <x-input-label for="catatan_admin" value="Alasan penolakan" />
                            <textarea id="catatan_admin" name="catatan_admin" rows="2" required class="{{ $inputClass }}">{{ old('catatan_admin') }}</textarea>
                            <button class="mt-2 px-4 py-2 bg-red-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest">Tolak</button>
                        </form>
                    </div>
                @endif

                @if ($peminjaman->status === 'disetujui' && $peminjaman->status_deposit === 'belum_dibayar')
                    <div class="mt-6">
                        <h3 class="font-semibold">Pembayaran deposit Rp {{ number_format($peminjaman->nominal_deposit, 0, ',', '.') }}</h3>

                        @unless ($peminjaman->tanggal_bayar)
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Peminjam belum melaporkan pembayaran. Jika uang sudah diterima langsung, Anda dapat mengonfirmasi dengan memilih metode.</p>
                        @endunless

                        <div class="mt-3 flex flex-col sm:flex-row gap-6">
                            <form method="POST" action="{{ route('admin.peminjamans.bayar.konfirmasi', $peminjaman) }}">
                                @csrf
                                @unless ($peminjaman->tanggal_bayar)
                                    <x-input-label for="metode_bayar" value="Metode" />
                                    <select id="metode_bayar" name="metode_bayar" class="{{ $inputClass }}">
                                        <option value="tunai">Tunai</option>
                                        <option value="transfer">Transfer</option>
                                    </select>
                                @endunless
                                <x-primary-button class="mt-3">Konfirmasi pembayaran</x-primary-button>
                            </form>

                            @if ($peminjaman->tanggal_bayar)
                                <form method="POST" action="{{ route('admin.peminjamans.bayar.tolak', $peminjaman) }}" class="flex-1">
                                    @csrf
                                    <x-input-label for="alasan" value="Alasan menolak laporan pembayaran" />
                                    <textarea id="alasan" name="alasan" rows="2" required class="{{ $inputClass }}">{{ old('alasan') }}</textarea>
                                    <button class="mt-2 px-4 py-2 bg-red-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest">Tolak laporan</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endif

                @if ($peminjaman->status_deposit !== 'belum_dibayar')
                    @include('partials.rincian-deposit')
                @endif

                <div class="mt-6">
                    <a href="{{ route('admin.peminjamans.index') }}" class="text-sm underline text-gray-600 dark:text-gray-400">Kembali</a>
                </div>
            </div>

            @if ($peminjaman->status === 'aktif' && $peminjaman->status_perawatan === 'terabaikan')
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                    <div class="p-3 rounded bg-red-100 text-red-800 text-sm">
                        Lahan ini terabaikan. Periksa kondisi lahan secara langsung sebelum mengambil alih. Setelah diambil alih, setiap tindakan beserta biayanya dicatat dan dipotong dari deposit.
                    </div>
                    <form method="POST" action="{{ route('admin.peminjamans.ambil-alih', $peminjaman) }}" class="mt-4" onsubmit="return confirm('Ambil alih perawatan lahan ini?')">
                        @csrf
                        <button class="px-4 py-2 bg-red-600 rounded-md font-semibold text-xs text-white uppercase tracking-widest">Ambil alih perawatan</button>
                    </form>
                </div>
            @endif

            @if ($peminjaman->status === 'aktif' && $peminjaman->status_perawatan === 'diambil_alih')
                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="font-semibold">Catat tindakan perawatan admin</h3>
                    <form method="POST" action="{{ route('admin.peminjamans.perawatan.store', $peminjaman) }}" enctype="multipart/form-data" class="mt-3">
                        @csrf
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="tanggal" value="Tanggal" />
                                <x-text-input id="tanggal" name="tanggal" type="date" class="block mt-1 w-full" :value="old('tanggal', today()->toDateString())" max="{{ today()->toDateString() }}" required />
                            </div>
                            <div>
                                <x-input-label for="kegiatan" value="Kegiatan" />
                                <select id="kegiatan" name="kegiatan" required class="{{ $inputClass }}">
                                    @foreach (['menyiram', 'pemupukan', 'penyiangan', 'pengendalian_hama', 'lainnya'] as $k)
                                        <option value="{{ $k }}" @selected(old('kegiatan') === $k)>{{ ucfirst(str_replace('_', ' ', $k)) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <x-input-label for="biaya" value="Biaya (Rp)" />
                                <x-text-input id="biaya" name="biaya" type="number" min="0" step="500" class="block mt-1 w-full" :value="old('biaya', 0)" required />
                            </div>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="catatan" value="Catatan (opsional)" />
                            <textarea id="catatan" name="catatan" rows="2" class="{{ $inputClass }}">{{ old('catatan') }}</textarea>
                        </div>
                        <div class="mt-4">
                            <x-input-label for="foto" value="Foto (opsional)" />
                            <input id="foto" name="foto" type="file" accept=".jpg,.jpeg,.png,.webp" class="{{ $fileClass }}">
                        </div>
                        <x-primary-button class="mt-4">Simpan tindakan</x-primary-button>
                    </form>
                </div>
            @endif

            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6 text-gray-900 dark:text-gray-100">
                @include('partials.riwayat-perawatan', ['admin' => true])
            </div>
        </div>
    </div>
</x-app-layout>
