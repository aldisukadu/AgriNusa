<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function __construct(private PeminjamanService $service) {}

    public function index(Request $request): View
    {
        $status = $request->query('status');

        $peminjamans = Peminjaman::with(['user', 'lahan.greenHouse'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.peminjamans.index', compact('peminjamans', 'status'));
    }

    public function show(Peminjaman $peminjaman): View
    {
        $peminjaman->load(['user', 'lahan.greenHouse']);

        $bersaing = Peminjaman::where('status', Peminjaman::STATUS_MENUNGGU)
            ->where('lahan_id', $peminjaman->lahan_id)
            ->where('id', '!=', $peminjaman->id)
            ->where('tanggal_mulai', '<=', $peminjaman->tanggal_selesai->toDateString())
            ->where('tanggal_selesai', '>=', $peminjaman->tanggal_mulai->toDateString())
            ->count();

        return view('admin.peminjamans.show', compact('peminjaman', 'bersaing'));
    }

    public function setujui(Peminjaman $peminjaman): RedirectResponse
    {
        $p = $this->service->setujui($peminjaman);

        return redirect()->route('admin.peminjamans.show', $p)
            ->with('success', 'Pengajuan disetujui. Batas pembayaran: '.$p->batas_bayar->format('d/m/Y H:i').'.');
    }

    public function tolak(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $data = $request->validate(['catatan_admin' => ['required', 'string', 'max:500']]);

        $this->service->tolak($peminjaman, $data['catatan_admin']);

        return redirect()->route('admin.peminjamans.show', $peminjaman)->with('success', 'Pengajuan ditolak.');
    }
}
