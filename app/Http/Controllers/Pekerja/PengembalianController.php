<?php

namespace App\Http\Controllers\Pekerja;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Services\PengembalianService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PengembalianController extends Controller
{
    public function __construct(private PengembalianService $service) {}

    public function kembalikan(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $data = $request->validate([
            'kondisi_kembali' => ['required', 'string', 'max:1000'],
            'foto_kembali' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $this->service->kembalikan($peminjaman, $data, $request->file('foto_kembali'));

        return redirect()->route('pekerja.peminjamans.show', $peminjaman)
            ->with('success', 'Pengembalian lahan dicatat. Lahan menunggu pemeriksaan.');
    }

    public function catatPembersihan(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:'.today()->toDateString()],
            'biaya' => ['required', 'integer', 'min:0', 'max:10000000'],
            'catatan' => ['required', 'string', 'max:1000'],
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $this->service->catatPembersihan($peminjaman, $request->user(), $data, $request->file('foto'));

        return redirect()->route('pekerja.peminjamans.show', $peminjaman)
            ->with('success', 'Catatan pembersihan tersimpan.');
    }
}
