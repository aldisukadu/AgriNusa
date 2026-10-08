<?php

namespace Database\Seeders;

use App\Models\GreenHouse;
use App\Models\Lahan;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $peminjam = User::firstOrNew(['email' => 'peminjam@example.com']);
        $peminjam->name = 'Peminjam Demo';
        $peminjam->password = 'password';
        $peminjam->status_pengguna = 'mahasiswa';
        $peminjam->no_hp = '081234567890';
        $peminjam->role = User::ROLE_PEMINJAM;
        $peminjam->save();

        $gh1 = GreenHouse::firstOrCreate(
            ['nama' => 'Green House A'],
            ['lokasi' => 'Belakang Gedung Pertanian', 'keterangan' => 'Tanaman sayuran daun.']
        );
        $gh2 = GreenHouse::firstOrCreate(
            ['nama' => 'Green House B'],
            ['lokasi' => 'Samping Laboratorium', 'keterangan' => 'Tanaman buah dan hidroponik.']
        );

        foreach ([[$gh1, 'A', 150000], [$gh2, 'B', 200000]] as [$gh, $huruf, $deposit]) {
            foreach (range(1, 4) as $n) {
                Lahan::firstOrCreate(
                    ['kode' => sprintf('GH%s-%02d', $huruf, $n)],
                    [
                        'green_house_id' => $gh->id,
                        'luas' => 4.00,
                        'media_tanam' => $n % 2 ? 'Tanah campur kompos' : 'Cocopeat',
                        'deposit' => $deposit,
                        'status' => Lahan::STATUS_TERSEDIA,
                    ]
                );
            }
        }
    }
}
