@php $selesai = $peminjaman->status === 'dikembalikan'; @endphp
<div class="mt-6">
    <h3 class="font-semibold">Rincian deposit</h3>
    <dl class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-2 text-sm">
        <div>
            <dt class="text-gray-500">Deposit</dt>
            <dd>Rp {{ number_format($peminjaman->nominal_deposit, 0, ',', '.') }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Biaya pembersihan oleh admin</dt>
            <dd>Rp {{ number_format($peminjaman->total_biaya_pembersihan, 0, ',', '.') }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">{{ $selesai ? 'Sisa deposit dikembalikan' : 'Perkiraan sisa deposit' }}</dt>
            <dd class="font-semibold">Rp {{ number_format($peminjaman->saldo_deposit, 0, ',', '.') }}</dd>
        </div>
    </dl>
    @if ($peminjaman->kekurangan_biaya > 0)
        <div class="mt-2 p-3 rounded bg-red-100 text-red-800 text-sm">
            Biaya pembersihan melebihi deposit sebesar Rp {{ number_format($peminjaman->kekurangan_biaya, 0, ',', '.') }}.
        </div>
    @endif
    @if ($selesai && $peminjaman->tanggal_deposit_selesai)
        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
            Deposit diselesaikan {{ $peminjaman->tanggal_deposit_selesai->format('d/m/Y H:i') }}.
        </p>
    @endif
</div>
