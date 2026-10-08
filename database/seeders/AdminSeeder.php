<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        // role tidak mass-assignable, jadi diisi eksplisit.
        $admin = User::firstOrNew(['email' => 'admin@example.com']);
        $admin->name = 'Administrator';
        $admin->password = 'password'; // di-hash otomatis oleh cast. GANTI sebelum dipakai di luar demo lokal.
        $admin->role = User::ROLE_ADMIN;
        $admin->save();
    }
}
