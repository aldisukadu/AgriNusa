<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("ALTER TABLE perawatan_logs MODIFY kegiatan ENUM('menyiram', 'pemupukan', 'penyiangan', 'pengendalian_hama', 'lainnya', 'pembersihan') NOT NULL");
    }

    public function down(): void
    {
        // Keep the enum value to avoid invalidating existing pembersihan records.
    }
};
