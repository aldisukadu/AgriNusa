<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Services\PembayaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PembayaranController extends Controller
{
    public function __construct(private PembayaranService $service) {}

    public function konfirmasi(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $data = $request->validate([
            'metode_bayar' => ['nullable', Rule::in(['tunai', 'transfer'])],
        ]);

        $this->service->konfirmasi($peminjaman, $data['metode_bayar'] ?? null);

        return redirect()->route('admin.peminjamans.show', $peminjaman)
            ->with('success', 'Pembayaran dikonfirmasi. Peminjaman kini aktif.');
    }

    public function tolak(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $data = $request->validate(['alasan' => ['required', 'string', 'max:500']]);

        $this->service->tolak($peminjaman, $data['alasan']);

        return redirect()->route('admin.peminjamans.show', $peminjaman)
            ->with('success', 'Laporan pembayaran ditolak.');
    }
}
