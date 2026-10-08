<?php

namespace App\Services;

use App\Models\AktivitasLog;
use App\Models\Peminjaman;
use Illuminate\Support\Facades\DB;

class JadwalService
{
    public function batalkanPembayaranKadaluarsa(): int
    {
        $ids = Peminjaman::where('status', Peminjaman::STATUS_DISETUJUI)
            ->where('status_deposit', Peminjaman::DEPOSIT_BELUM_DIBAYAR)
            ->whereNull('tanggal_bayar')
            ->where('batas_bayar', '<', now())
            ->pluck('id');

        return $ids->sum(fn ($id) => DB::transaction(function () use ($id) {
            $p = Peminjaman::whereKey($id)->lockForUpdate()->first();

            if (! $p || $p->status !== Peminjaman::STATUS_DISETUJUI
                || $p->tanggal_bayar !== null
                || $p->batas_bayar === null
                || ! now()->gt($p->batas_bayar)) {
                return 0;
            }

            $alasan = 'Dibatalkan otomatis: pembayaran belum dilaporkan sampai batas waktu.';
            $p->status = Peminjaman::STATUS_DIBATALKAN;
            $p->catatan_admin = trim(($p->catatan_admin ? $p->catatan_admin.' ' : '').$alasan);
            $p->save();

            AktivitasLog::catat('batalkan_otomatis', $p->id, $p->lahan_id,
                ['status' => Peminjaman::STATUS_DISETUJUI],
                ['status' => Peminjaman::STATUS_DIBATALKAN]
            );

            return 1;
        }));
    }

    public function tandaiKembaliKadaluarsa(): int
    {
        $batasHari = (int) config('greenhouse.batas_kembali_hari');

        $ids = Peminjaman::where('status', Peminjaman::STATUS_AKTIF)
            ->where('tanggal_selesai', '<', today()->subDays($batasHari)->toDateString())
            ->pluck('id');

        return $ids->sum(fn ($id) => DB::transaction(function () use ($id, $batasHari) {
            $p = Peminjaman::whereKey($id)->lockForUpdate()->first();

            if (! $p || $p->status !== Peminjaman::STATUS_AKTIF
                || ! today()->gt($p->tanggal_selesai->copy()->addDays($batasHari))) {
                return 0;
            }

            $p->status = Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN;
            $p->tanggal_kembali = today();
            $p->kondisi_kembali = 'Ditandai otomatis: lahan tidak dikembalikan sampai batas waktu.';
            $p->save();

            AktivitasLog::catat('kembalikan_otomatis', $p->id, $p->lahan_id,
                ['status' => Peminjaman::STATUS_AKTIF],
                ['status' => Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN]
            );

            return 1;
        }));
    }
}
