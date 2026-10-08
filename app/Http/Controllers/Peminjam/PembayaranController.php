<?php

namespace App\Http\Controllers\Peminjam;

use App\Http\Controllers\Controller;
use App\Models\Peminjaman;
use App\Services\PembayaranService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class PembayaranController extends Controller
{
    public function __construct(private PembayaranService $service) {}

    public function store(Request $request, Peminjaman $peminjaman): RedirectResponse
    {
        Gate::authorize('update', $peminjaman);

        $data = $request->validate([
            'metode_bayar' => ['required', Rule::in(['tunai', 'transfer'])],
            'bukti_bayar' => [
                Rule::requiredIf($request->input('metode_bayar') === 'transfer'),
                'nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096',
            ],
        ]);

        $this->service->lapor($peminjaman, $data['metode_bayar'], $request->file('bukti_bayar'));

        return redirect()->route('peminjam.peminjamans.show', $peminjaman)
            ->with('success', 'Pembayaran dilaporkan. Tunggu konfirmasi admin.');
    }
}
