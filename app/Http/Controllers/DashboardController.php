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
            'jumlah' => [
                'pengajuan' => Peminjaman::where('status', Peminjaman::STATUS_MENUNGGU)->count(),
                'laporanBayar' => $this->queryLaporanBayar()->count(),
                'pemeriksaan' => Peminjaman::where('status', Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN)->count(),
                'aktif' => Peminjaman::where('status', Peminjaman::STATUS_AKTIF)->count(),
                'lahanTersedia' => Lahan::where('status', Lahan::STATUS_TERSEDIA)->count(),
                'lahanPerawatan' => Lahan::where('status', Lahan::STATUS_PERAWATAN)->count(),
                'lahanTotal' => Lahan::count(),
            ],
            'pengajuan' => Peminjaman::with(['user', 'lahan'])
                ->where('status', Peminjaman::STATUS_MENUNGGU)
                ->oldest()->limit(5)->get(),
            'laporanBayar' => $this->queryLaporanBayar()
                ->with(['user', 'lahan'])
                ->oldest('tanggal_bayar')->limit(5)->get(),
            'pemeriksaan' => Peminjaman::with(['user', 'lahan'])
                ->where('status', Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN)
                ->oldest('tanggal_kembali')->limit(5)->get(),
            'lahans' => Lahan::with('greenHouse')->orderBy('kode')->get(),
        ]);
    }

    public function peminjam(Request $request): View
    {
        $user = $request->user();

        $menungguBayar = $user->peminjamans()
            ->where('status', Peminjaman::STATUS_DISETUJUI)
            ->where('status_deposit', Peminjaman::DEPOSIT_BELUM_DIBAYAR)
            ->with('lahan')
            ->orderBy('batas_bayar')
            ->get();

        $aktif = $user->peminjamans()
            ->where('status', Peminjaman::STATUS_AKTIF)
            ->with('lahan')
            ->orderBy('tanggal_selesai')
            ->get();

        return view('peminjam.dashboard', [
            'menungguBayar' => $menungguBayar,
            'aktif' => $aktif,
            'jumlahMenunggu' => $user->peminjamans()->where('status', Peminjaman::STATUS_MENUNGGU)->count(),
            'jumlahPemeriksaan' => $user->peminjamans()->where('status', Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN)->count(),
            'batasKembali' => (int) config('greenhouse.batas_kembali_hari'),
        ]);
    }

    public function pekerja(): View
    {
        $batasKembali = (int) config('greenhouse.batas_kembali_hari');

        return view('pekerja.dashboard', [
            'pemeriksaan' => Peminjaman::with(['user', 'lahan'])
                ->where('status', Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN)
                ->latest('tanggal_kembali')
                ->limit(5)
                ->get(),
            'pengembalianTerlambat' => Peminjaman::with(['user', 'lahan'])
                ->where('status', Peminjaman::STATUS_AKTIF)
                ->whereDate('tanggal_selesai', '<', today()->subDays($batasKembali))
                ->oldest('tanggal_selesai')
                ->limit(5)
                ->get(),
            'jumlahPemeriksaan' => Peminjaman::where('status', Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN)->count(),
            'jumlahAktif' => Peminjaman::where('status', Peminjaman::STATUS_AKTIF)->count(),
        ]);
    }

    private function queryLaporanBayar()
    {
        return Peminjaman::where('status', Peminjaman::STATUS_DISETUJUI)
            ->where('status_deposit', Peminjaman::DEPOSIT_BELUM_DIBAYAR)
            ->whereNotNull('tanggal_bayar');
    }
}
