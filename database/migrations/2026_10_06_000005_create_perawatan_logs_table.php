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
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->enum('pelaksana', ['peminjam', 'admin']);
            $table->date('tanggal');
            $table->enum('kegiatan', [
                'menyiram', 'pemupukan', 'penyiangan', 'pengendalian_hama', 'lainnya', 'pembersihan',
            ]);
            $table->unsignedBigInteger('biaya')->default(0);
            $table->text('catatan')->nullable();
            $table->string('foto')->nullable();
            $table->timestamps();

            $table->index(['peminjaman_id', 'pelaksana'], 'perawatan_logs_peminjaman_pelaksana_index');
        });

        // SQLite (dipakai test) tidak mendukung ADD CONSTRAINT.
        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'])) {
            DB::statement("ALTER TABLE perawatan_logs ADD CONSTRAINT perawatan_logs_biaya_chk CHECK (pelaksana = 'admin' OR biaya = 0)");
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('perawatan_logs');
    }
};
