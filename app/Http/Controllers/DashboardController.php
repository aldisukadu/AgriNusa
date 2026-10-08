<?php

namespace App\Http\Controllers;

use App\Models\Lahan;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function admin(): View
    {
        return view('admin.dashboard', [
            'menunggu' => Peminjaman::where('status', Peminjaman::STATUS_MENUNGGU)->count(),
            'pemeriksaan' => Peminjaman::where('status', Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN)->count(),
            'aktif' => Peminjaman::where('status', Peminjaman::STATUS_AKTIF)->count(),
            'lahanTersedia' => Lahan::where('status', Lahan::STATUS_TERSEDIA)->count(),
            'lahanTotal' => Lahan::count(),
        ]);
    }

    public function peminjam(Request $request): View
    {
        $user = $request->user();

        return view('peminjam.dashboard', [
            'aktif' => $user->peminjamans()->where('status', Peminjaman::STATUS_AKTIF)->count(),
            'menunggu' => $user->peminjamans()->where('status', Peminjaman::STATUS_MENUNGGU)->count(),
            'disetujui' => $user->peminjamans()->where('status', Peminjaman::STATUS_DISETUJUI)->count(),
        ]);
    }
}
