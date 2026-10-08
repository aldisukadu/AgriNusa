<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AktivitasLog;
use App\Models\GreenHouse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GreenHouseController extends Controller
{
    private const KOLOM = ['nama', 'lokasi', 'keterangan'];

    public function index(): View
    {
        return view('admin.green-houses.index', [
            'greenHouses' => GreenHouse::withCount('lahans')->orderBy('nama')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.green-houses.form', ['greenHouse' => new GreenHouse]);
    }

    public function store(Request $request): RedirectResponse
    {
        $greenHouse = GreenHouse::create($this->validated($request));

        AktivitasLog::catat('tambah_green_house', null, null, null, $greenHouse->only(self::KOLOM));

        return redirect()->route('admin.green-houses.index')->with('success', 'Green house ditambahkan.');
    }

    public function edit(GreenHouse $greenHouse): View
    {
        return view('admin.green-houses.form', compact('greenHouse'));
    }

    public function update(Request $request, GreenHouse $greenHouse): RedirectResponse
    {
        $lama = $greenHouse->only(self::KOLOM);
        $greenHouse->update($this->validated($request));

        AktivitasLog::catat('ubah_green_house', null, null, $lama, $greenHouse->only(self::KOLOM));

        return redirect()->route('admin.green-houses.index')->with('success', 'Green house diperbarui.');
    }

    public function destroy(GreenHouse $greenHouse): RedirectResponse
    {
        if ($greenHouse->lahans()->exists()) {
            return back()->withErrors(['hapus' => 'Hapus semua lahan di green house ini terlebih dahulu.']);
        }

        $lama = $greenHouse->only(['id', ...self::KOLOM]);
        $greenHouse->delete();

        AktivitasLog::catat('hapus_green_house', null, null, $lama);

        return redirect()->route('admin.green-houses.index')->with('success', 'Green house dihapus.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'lokasi' => ['required', 'string', 'max:255'],
            'keterangan' => ['nullable', 'string', 'max:1000'],
        ]);
    }
}
