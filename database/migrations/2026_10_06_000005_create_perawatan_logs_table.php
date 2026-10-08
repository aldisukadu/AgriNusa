<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perawatan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('peminjaman_id')->constrained('peminjamans')->restrictOnDelete();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete(); // pelaksana
            $table->enum('pelaksana', ['peminjam', 'admin']);
            $table->date('tanggal');
            $table->enum('kegiatan', ['menyiram', 'pemupukan', 'penyiangan', 'pengendalian_hama', 'lainnya']);
            $table->unsignedBigInteger('biaya')->default(0); // 0 jika oleh peminjam
            $table->text('catatan')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();

            // Deteksi terabaikan membaca created_at (bukan tanggal yang diisi peminjam)
            $table->index(['peminjaman_id', 'pelaksana', 'created_at'], 'perawatan_logs_deteksi_index');
        });

        // Biaya hanya boleh > 0 jika pelaksana = admin.
        // MariaDB >= 10.2.1 dan MySQL >= 8.0.16 menegakkan CHECK; versi lebih lama mengabaikannya,
        // jadi validasi di aplikasi tetap wajib.
        DB::statement("ALTER TABLE perawatan_logs ADD CONSTRAINT perawatan_logs_biaya_chk CHECK (pelaksana = 'admin' OR biaya = 0)");
    }

    public function down(): void
    {
        Schema::dropIfExists('perawatan_logs');
    }
};
