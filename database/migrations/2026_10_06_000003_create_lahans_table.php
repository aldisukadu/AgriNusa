<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lahans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('green_house_id')->constrained('green_houses')->restrictOnDelete();
            $table->string('kode', 30)->unique();
            $table->decimal('luas', 8, 2);
            $table->string('media_tanam');
            $table->unsignedBigInteger('deposit');
            $table->enum('status', ['tersedia', 'perawatan'])->default('tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lahans');
    }
};
