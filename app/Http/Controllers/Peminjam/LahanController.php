<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Lahan;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LahanController extends Controller
{
    public function index(Request $request): View
    {
        $lahans = Lahan::with([
            'greenHouse',
            'peminjamans' => fn ($q) => $q->memblokir()
                ->whereDate('tanggal_selesai', '>=', today())
                ->orderBy('tanggal_mulai'),
        ])->orderBy('kode')->get();

        $mulai = $request->date('mulai');
        $selesai = $request->date('selesai');
        $tersedia = null;

        if ($mulai && $selesai && $selesai->gte($mulai)) {
            $terisi = Peminjaman::memblokir()
                ->where('tanggal_mulai', '<=', $selesai->toDateString())
                ->where('tanggal_selesai', '>=', $mulai->toDateString())
                ->pluck('lahan_id');

            $tersedia = $lahans
                ->filter(fn ($l) => $l->status === Lahan::STATUS_TERSEDIA && ! $terisi->contains($l->id))
                ->pluck('id');
        }

        return view('peminjam.lahans.index', [
            'lahans' => $lahans,
            'tersedia' => $tersedia,
            'mulai' => $mulai?->toDateString(),
            'selesai' => $selesai?->toDateString(),
        ]);
    }
}
