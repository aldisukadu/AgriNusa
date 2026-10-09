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

        DB::statement("ALTER TABLE users MODIFY role ENUM('admin', 'pekerja', 'peminjam') NOT NULL DEFAULT 'peminjam'");
        DB::statement("ALTER TABLE perawatan_logs MODIFY pelaksana ENUM('peminjam', 'pekerja', 'admin') NOT NULL");

        $constraintExists = DB::table('information_schema.TABLE_CONSTRAINTS')
            ->where('CONSTRAINT_SCHEMA', DB::getDatabaseName())
            ->where('TABLE_NAME', 'perawatan_logs')
            ->where('CONSTRAINT_NAME', 'perawatan_logs_biaya_chk')
            ->exists();

        if ($constraintExists) {
            DB::statement('ALTER TABLE perawatan_logs DROP CHECK perawatan_logs_biaya_chk');
        }

        DB::statement("ALTER TABLE perawatan_logs ADD CONSTRAINT perawatan_logs_biaya_chk CHECK (pelaksana <> 'peminjam' OR biaya = 0)");
    }

    public function down(): void
    {
        // Keep worker roles and maintenance records intact.
    }
};
