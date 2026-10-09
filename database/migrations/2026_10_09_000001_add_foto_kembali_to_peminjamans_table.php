<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('peminjamans', 'foto_kembali')) {
            Schema::table('peminjamans', function (Blueprint $table) {
                $table->string('foto_kembali')->nullable();
            });
        }
    }

    public function down(): void
    {
        // The column is part of the original peminjamans table definition.
    }
};
