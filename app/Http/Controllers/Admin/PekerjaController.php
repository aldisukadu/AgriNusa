<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class PekerjaController extends Controller
{
    public function index(): View
    {
        $pekerjas = User::where('role', User::ROLE_PEKERJA)
            ->latest()
            ->paginate(15);

        return view('admin.pekerja.index', compact('pekerjas'));
    }

    public function create(): View
    {
        return view('admin.pekerja.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'no_hp' => ['nullable', 'regex:/^\+?[0-9]{8,18}$/'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $pekerja = new User([
            'name' => $data['name'],
            'email' => $data['email'],
            'no_hp' => $data['no_hp'] ?? null,
        ]);
        $pekerja->password = $data['password'];
        $pekerja->role = User::ROLE_PEKERJA;
        $pekerja->save();

        return redirect()->route('admin.pekerja.index')
            ->with('success', 'Akun pekerja berhasil dibuat.');
    }
}
