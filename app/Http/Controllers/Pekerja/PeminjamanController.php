<?php

namespace App\Http\Controllers\Pekerja;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $statuses = [
            Peminjaman::STATUS_MENUNGGU,
            Peminjaman::STATUS_DISETUJUI,
            Peminjaman::STATUS_AKTIF,
            Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN,
            Peminjaman::STATUS_DIKEMBALIKAN,
            Peminjaman::STATUS_DITOLAK,
            Peminjaman::STATUS_DIBATALKAN,
        ];

        $peminjamans = Peminjaman::with(['user', 'lahan.greenHouse'])
            ->when(in_array($status, $statuses, true), fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pekerja.peminjamans.index', compact('peminjamans', 'status', 'statuses'));
    }

    public function show(Peminjaman $peminjaman): View
    {
        $peminjaman->load(['user', 'lahan.greenHouse']);

        return view('admin.peminjamans.show', [
            'peminjaman' => $peminjaman,
            'bersaing' => 0,
            'isAdmin' => false,
        ]);
    }
}
