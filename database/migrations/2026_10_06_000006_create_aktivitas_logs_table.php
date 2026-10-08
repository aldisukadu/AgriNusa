<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('aktivitas_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('peminjaman_id')->nullable()->constrained('peminjamans')->nullOnDelete();
            $table->foreignId('lahan_id')->nullable()->constrained('lahans')->nullOnDelete();
            $table->string('aksi');
            $table->json('data_lama')->nullable();
            $table->json('data_baru')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('aktivitas_logs');
    }
};
