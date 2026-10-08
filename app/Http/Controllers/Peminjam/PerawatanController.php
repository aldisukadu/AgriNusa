<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\PerawatanLog;
use App\Models\Peminjaman;
use App\Services\PerawatanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PerawatanController extends Controller
{
    public function __construct(private PerawatanService $service) {}

    public function store(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        Gate::authorize('update', $peminjaman);

        $data = $request->validate([
            'tanggal' => [
                'required', 'date',
                'after_or_equal:'.today()->subDay()->toDateString(),
                'before_or_equal:'.today()->toDateString(),
            ],
            'kegiatan' => ['required', Rule::in(PerawatanLog::KEGIATAN)],
            'catatan' => ['nullable', 'string', 'max:1000'],
            'foto' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $this->service->catatPeminjam($peminjaman, $request->user(), $data, $request->file('foto'));

        return redirect()->route('peminjam.peminjamans.show', $peminjaman)
            ->with('success', 'Catatan perawatan tersimpan.');
    }
}
