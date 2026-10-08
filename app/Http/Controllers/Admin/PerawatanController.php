<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PerawatanLog;
use App\Models\Peminjaman;
use App\Services\PerawatanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerawatanController extends Controller
{
    public function __construct(private PerawatanService $service) {}

    public function ambilAlih(Peminjaman $peminjaman): RedirectResponse
    {
        $this->service->ambilAlih($peminjaman);

        return redirect()->route('admin.peminjamans.show', $peminjaman)
            ->with('success', 'Perawatan diambil alih. Catat setiap tindakan beserta biayanya.');
    }

    public function store(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        $data = $request->validate([
            'tanggal' => ['required', 'date', 'before_or_equal:'.today()->toDateString()],
            'kegiatan' => ['required', Rule::in(PerawatanLog::KEGIATAN)],
            'biaya' => ['required', 'integer', 'min:0', 'max:10000000'],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $this->service->catatAdmin($peminjaman, $request->user(), $data, $request->file('foto'));

        return redirect()->route('admin.peminjamans.show', $peminjaman)
            ->with('success', 'Tindakan perawatan dicatat. Biaya dipotong dari deposit.');
    }

    public function destroy(PerawatanLog $perawatanLog): RedirectResponse
    {
        $peminjamanId = $perawatanLog->peminjaman_id;

        $this->service->hapusCatatanAdmin($perawatanLog);

        return redirect()->route('admin.peminjamans.show', $peminjamanId)
            ->with('success', 'Catatan dihapus dan total biaya dihitung ulang.');
    }
}
