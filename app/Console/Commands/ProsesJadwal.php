<?php

namespace App\Console\Commands;

use App\Services\JadwalService;
use Illuminate\Console\Command;

class ProsesJadwal extends Command
{
    protected $signature = 'greenhouse:proses-jadwal';

    protected $description = 'Batalkan pembayaran yang lewat batas dan tandai lahan yang tidak dikembalikan';

    public function handle(JadwalService $jadwal): int
    {
        $dibatalkan = $jadwal->batalkanPembayaranKadaluarsa();
        $ditandai = $jadwal->tandaiKembaliKadaluarsa();

        $this->info("Dibatalkan: {$dibatalkan}. Ditandai dikembalikan: {$ditandai}.");

        return self::SUCCESS;
    }
}
