<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('peminjamans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('lahan_id')->constrained('lahans')->restrictOnDelete();

            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('jenis_tanaman');
            $table->enum('tujuan', ['praktikum', 'penelitian', 'budidaya']);

            $table->enum('status', [
                'menunggu', 'disetujui', 'aktif', 'ditolak',
                'menunggu_pemeriksaan', 'dikembalikan', 'dibatalkan',
            ])->default('menunggu');
            $table->text('catatan_admin')->nullable();
            $table->text('kondisi_kembali')->nullable();
            $table->string('foto_kembali')->nullable();
            $table->date('tanggal_kembali')->nullable();

            $table->unsignedBigInteger('nominal_deposit');
            $table->enum('status_deposit', [
                'belum_dibayar', 'dibayar', 'dikembalikan', 'dipotong', 'terpakai_habis',
            ])->default('belum_dibayar');
            $table->enum('metode_bayar', ['tunai', 'transfer'])->nullable();
            $table->string('bukti_bayar')->nullable();
            $table->dateTime('tanggal_bayar')->nullable();
            $table->dateTime('batas_bayar')->nullable();
            $table->dateTime('aktif_sejak')->nullable();

            $table->unsignedBigInteger('total_biaya_pembersihan')->default(0);
            $table->unsignedBigInteger('kekurangan_bayar')->default(0);
            $table->dateTime('tanggal_deposit_selesai')->nullable();

            $table->timestamps();

            $table->index(['lahan_id', 'tanggal_mulai', 'tanggal_selesai'], 'peminjamans_lahan_periode_index');
            $table->index(['status', 'batas_bayar'], 'peminjamans_status_batas_bayar_index');
            $table->index(['status', 'tanggal_selesai'], 'peminjamans_status_selesai_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('peminjamans');
    }
};
