<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrNew(['email' => 'admin@example.com']);
        $admin->name = 'Administrator';
        $admin->password = 'password'; // demo lokal saja
        $admin->role = User::ROLE_ADMIN;
        $admin->save();
    }
}
