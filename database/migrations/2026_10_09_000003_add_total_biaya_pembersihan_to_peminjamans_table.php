<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('peminjamans', 'total_biaya_pembersihan')) {
            return;
        }

        Schema::table('peminjamans', function (Blueprint $table) {
            $table->unsignedBigInteger('total_biaya_pembersihan')->default(0);
        });

        DB::table('peminjamans')
            ->select('id')
            ->orderBy('id')
            ->chunkById(100, function ($peminjamans) {
                foreach ($peminjamans as $peminjaman) {
                    $total = DB::table('perawatan_logs')
                        ->where('peminjaman_id', $peminjaman->id)
                        ->where('pelaksana', 'admin')
                        ->sum('biaya');

                    DB::table('peminjamans')
                        ->where('id', $peminjaman->id)
                        ->update(['total_biaya_pembersihan' => $total]);
                }
            });
    }

    public function down(): void
    {
        // Keep the value to preserve recorded cleaning totals.
    }
};
