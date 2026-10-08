<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Services\PengembalianService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PengembalianController extends Controller
{
    public function __construct(private PengembalianService $service) {}

    public function store(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        Gate::authorize('update', $peminjaman);

        $data = $request->validate([
            'kondisi_kembali' => ['required', 'string', 'max:1000'],
            'foto_kembali' => ['required', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $this->service->kembalikan($peminjaman, $data, $request->file('foto_kembali'));

        return redirect()->route('peminjam.peminjamans.show', $peminjaman)
            ->with('success', 'Pengembalian dicatat. Menunggu pemeriksaan admin.');
    }
}
