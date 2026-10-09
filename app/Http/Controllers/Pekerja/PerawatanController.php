<?php

namespace App\Http\Controllers\Pekerja;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Models\PerawatanLog;
use App\Services\PerawatanService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PerawatanController extends Controller
{
    public function __construct(private PerawatanService $service) {}

    public function store(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
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

        $this->service->catatPekerja($peminjaman, $request->user(), $data, $request->file('foto'));

        return redirect()->route('pekerja.peminjamans.show', $peminjaman)
            ->with('success', 'Catatan perawatan pekerja tersimpan.');
    }
}
