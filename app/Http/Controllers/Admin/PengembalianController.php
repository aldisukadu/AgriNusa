<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerawatanLog;
use App\Models\Peminjaman;
use App\Services\PengembalianService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PengembalianController extends Controller
{
    public function __construct(private PengembalianService $service) {}

    public function kembalikan(Peminjaman $peminjaman): RedirectResponse
    {
        $this->service->kembalikanOlehAdmin($peminjaman);

        return redirect()->route('admin.peminjamans.show', $peminjaman)
            ->with('success', 'Lahan ditandai dikembalikan. Lanjutkan pemeriksaan.');
    }

    public function catat(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:'.today()->toDateString()],
            'biaya' => ['required', 'integer', 'min:0', 'max:10000000'],
            'catatan' => ['required', 'string', 'max:1000'],
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $this->service->catatPembersihan($peminjaman, $request->user(), $data, $request->file('foto'));

        return redirect()->route('admin.peminjamans.show', $peminjaman)
            ->with('success', 'Pembersihan dicatat. Biaya dipotong dari deposit saat diselesaikan.');
    }

    public function hapus(PerawatanLog $perawatanLog): RedirectResponse
    {
        $peminjamanId = $perawatanLog->peminjaman_id;

        $this->service->hapusPembersihan($perawatanLog);

        return redirect()->route('admin.peminjamans.show', $peminjamanId)
            ->with('success', 'Catatan dihapus dan total biaya dihitung ulang.');
    }

    public function selesaikan(Peminjaman $peminjaman): RedirectResponse
    {
        $hasil = $this->service->selesaikan($peminjaman);

        $pesan = 'Peminjaman selesai. Sisa deposit Rp '.number_format($hasil['sisa'], 0, ',', '.').' dikembalikan ke peminjam.';
        if ($hasil['kekurangan'] > 0) {
            $pesan .= ' Kekurangan biaya Rp '.number_format($hasil['kekurangan'], 0, ',', '.').' belum terbayar.';
        }

        return redirect()->route('admin.peminjamans.show', $peminjaman)->with('success', $pesan);
    }
}
