<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'pekerja', 'peminjam'])->default('peminjam')->after('password');
            $table->enum('status_pengguna', ['mahasiswa', 'siswa', 'dosen', 'kelompok'])->nullable()->after('role');
            $table->string('no_hp', 20)->nullable()->after('status_pengguna');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'status_pengguna', 'no_hp']);
        });
    }
};
