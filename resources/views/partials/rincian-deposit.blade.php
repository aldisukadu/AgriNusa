<div class="mt-6">
    <h3 class="font-semibold">Rincian deposit</h3>
    <dl class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-x-6 gap-y-2 text-sm">
        <div>
            <dt class="text-gray-500">Deposit</dt>
            <dd>Rp {{ number_format($peminjaman->nominal_deposit, 0, ',', '.') }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Biaya perawatan oleh admin</dt>
            <dd>Rp {{ number_format($peminjaman->total_biaya_perawatan, 0, ',', '.') }}</dd>
        </div>
        <div>
            <dt class="text-gray-500">Perkiraan sisa deposit</dt>
            <dd class="font-semibold">Rp {{ number_format($peminjaman->saldo_deposit, 0, ',', '.') }}</dd>
        </div>
    </dl>
    @if ($peminjaman->kekurangan_biaya > 0)
        <div class="mt-2 p-3 rounded bg-red-100 text-red-800 text-sm">
            Biaya melebihi deposit sebesar Rp {{ number_format($peminjaman->kekurangan_biaya, 0, ',', '.') }}.
        </div>
    @endif
</div>
