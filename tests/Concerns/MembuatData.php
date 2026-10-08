<?php

namespace Tests\Concerns;

use App\Models\GreenHouse;
use App\Models\Lahan;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Support\Str;

trait MembuatData
{
    protected function buatUser(string $role = 'peminjam'): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    protected function buatLahan(array $override = []): Lahan
    {
        $gh = GreenHouse::firstOrCreate(['nama' => 'GH Test'], ['lokasi' => 'Test']);

        return Lahan::create(array_merge([
            'green_house_id' => $gh->id,
            'kode' => 'T-'.Str::random(6),
            'luas' => 4,
            'media_tanam' => 'Tanah',
            'deposit' => 100000,
        ], $override));
    }

    protected function buatPeminjaman(User $user, Lahan $lahan, string $status, string $mulai, string $selesai): Peminjaman
    {
        return Peminjaman::forceCreate([
            'user_id' => $user->id,
            'lahan_id' => $lahan->id,
            'tanggal_mulai' => $mulai,
            'tanggal_selesai' => $selesai,
            'jenis_tanaman' => 'Selada',
            'tujuan' => 'praktikum',
            'status' => $status,
            'nominal_deposit' => $lahan->deposit,
        ]);
    }

    protected function hari(int $tambah): string
    {
        return now()->addDays($tambah)->toDateString();
    }
}
