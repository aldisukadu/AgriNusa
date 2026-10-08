<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\GreenHouse;
use App\Models\Lahan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LahanController extends Controller
{
    private const KOLOM = ['green_house_id', 'kode', 'luas', 'media_tanam', 'deposit', 'status'];

    public function index(): View
    {
        return view('admin.lahans.index', [
            'lahans' => Lahan::with('greenHouse')->orderBy('kode')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.lahans.form', [
            'lahan' => new Lahan(['status' => Lahan::STATUS_TERSEDIA]),
            'greenHouses' => GreenHouse::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $lahan = Lahan::create($this->validated($request));

        AktivitasLog::catat('tambah_lahan', null, $lahan->id, null, $lahan->only(self::KOLOM));

        return redirect()->route('admin.lahans.index')->with('success', 'Lahan ditambahkan.');
    }

    public function edit(Lahan $lahan): View
    {
        return view('admin.lahans.form', [
            'lahan' => $lahan,
            'greenHouses' => GreenHouse::orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, Lahan $lahan): RedirectResponse
    {
        $lama = $lahan->only(self::KOLOM);
        $lahan->update($this->validated($request, $lahan));

        AktivitasLog::catat('ubah_lahan', null, $lahan->id, $lama, $lahan->only(self::KOLOM));

        return redirect()->route('admin.lahans.index')->with('success', 'Lahan diperbarui.');
    }

    public function destroy(Lahan $lahan): RedirectResponse
    {
        if ($lahan->peminjamans()->exists()) {
            return back()->withErrors(['hapus' => 'Lahan sudah punya riwayat peminjaman. Ubah statusnya menjadi perawatan, jangan dihapus.']);
        }

        $lama = $lahan->only(['id', ...self::KOLOM]);
        $lahan->delete();

        AktivitasLog::catat('hapus_lahan', null, null, $lama);

        return redirect()->route('admin.lahans.index')->with('success', 'Lahan dihapus.');
    }

    private function validated(Request $request, ?Lahan $lahan = null): array
    {
        return $request->validate([
            'green_house_id' => ['required', 'exists:green_houses,id'],
            'kode' => ['required', 'string', 'max:30', Rule::unique('lahans', 'kode')->ignore($lahan?->id)],
            'luas' => ['required', 'numeric', 'min:0.1', 'max:99999'],
            'media_tanam' => ['required', 'string', 'max:100'],
            'deposit' => ['required', 'integer', 'min:0', 'max:100000000'],
            'status' => ['required', Rule::in([Lahan::STATUS_TERSEDIA, Lahan::STATUS_PERAWATAN])],
        ]);
    }
}
