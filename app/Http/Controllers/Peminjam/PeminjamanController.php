<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Lahan;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PeminjamanController extends Controller
{
    public function __construct(private PeminjamanService $service) {}

    public function index(Request $request): View
    {
        $peminjamans = $request->user()->peminjamans()
            ->with('lahan.greenHouse')
            ->latest()
            ->paginate(10);

        return view('peminjam.peminjamans.index', compact('peminjamans'));
    }

    public function create(Request $request): View
    {
        return view('peminjam.peminjamans.create', [
            'lahans' => Lahan::with('greenHouse')->where('status', Lahan::STATUS_TERSEDIA)->orderBy('kode')->get(),
            'dipilih' => $request->query('lahan'),
            'mulai' => $request->query('mulai'),
            'selesai' => $request->query('selesai'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'lahan_id' => ['required', 'exists:lahans,id'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'jenis_tanaman' => ['required', 'string', 'max:100'],
            'tujuan' => ['required', Rule::in(['praktikum', 'penelitian', 'budidaya'])],
        ]);

        $peminjaman = $this->service->ajukan($request->user(), $data);

        return redirect()->route('peminjam.peminjamans.show', $peminjaman)
            ->with('success', 'Pengajuan terkirim. Tunggu persetujuan admin.');
    }

    public function show(Peminjaman $peminjaman): View
    {
        Gate::authorize('view', $peminjaman);

        $peminjaman->load('lahan.greenHouse');

        return view('peminjam.peminjamans.show', compact('peminjaman'));
    }
}
