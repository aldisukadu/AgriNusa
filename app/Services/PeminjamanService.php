<?php

namespace App\Services;

use App\Models\AktivitasLog;
use App\Models\Lahan;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PeminjamanService
{
    public function ajukan(User $user, array $data): Peminjaman
    {
        return DB::transaction(function () use ($user, $data) {
            $lahan = Lahan::whereKey($data['lahan_id'])->lockForUpdate()->firstOrFail();

            if ($lahan->status !== Lahan::STATUS_TERSEDIA) {
                throw ValidationException::withMessages(['lahan_id' => 'Lahan sedang dalam perawatan.']);
            }

            if (Peminjaman::bentrok($lahan->id, $data['tanggal_mulai'], $data['tanggal_selesai'])->exists()) {
                throw ValidationException::withMessages(['tanggal_mulai' => 'Jadwal bentrok dengan peminjaman lain.']);
            }

            $peminjaman = Peminjaman::create([
                'user_id' => $user->id,
                'lahan_id' => $lahan->id,
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'jenis_tanaman' => $data['jenis_tanaman'],
                'tujuan' => $data['tujuan'],
                'nominal_deposit' => $lahan->deposit,
            ]);

            AktivitasLog::catat('ajukan_peminjaman', $peminjaman->id, $lahan->id, null, [
                'tanggal_mulai' => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'nominal_deposit' => $lahan->deposit,
            ]);

            return $peminjaman;
        });
    }

    public function setujui(Peminjaman $peminjaman): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman) {
            $lahan = Lahan::whereKey($peminjaman->lahan_id)->lockForUpdate()->firstOrFail();
            $p = Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();

            if ($p->status !== Peminjaman::STATUS_MENUNGGU) {
                throw ValidationException::withMessages(['status' => 'Pengajuan ini sudah diproses.']);
            }

            if ($lahan->status !== Lahan::STATUS_TERSEDIA) {
                throw ValidationException::withMessages(['status' => 'Lahan sedang dalam perawatan.']);
            }

            $bentrok = Peminjaman::bentrok(
                $p->lahan_id,
                $p->tanggal_mulai->toDateString(),
                $p->tanggal_selesai->toDateString(),
                $p->id
            )->exists();

            if ($bentrok) {
                throw ValidationException::withMessages(['tanggal_mulai' => 'Jadwal bentrok dengan peminjaman yang sudah disetujui.']);
            }

            $p->status = Peminjaman::STATUS_DISETUJUI;
            $p->batas_bayar = now()->addHours(config('greenhouse.batas_bayar_jam'));
            $p->save();

            AktivitasLog::catat('setujui_peminjaman', $p->id, $p->lahan_id,
                ['status' => Peminjaman::STATUS_MENUNGGU],
                ['status' => $p->status, 'batas_bayar' => $p->batas_bayar->toDateTimeString()]
            );

            return $p;
        });
    }

    public function tolak(Peminjaman $peminjaman, string $catatan): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman, $catatan) {
            $p = Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();

            if ($p->status !== Peminjaman::STATUS_MENUNGGU) {
                throw ValidationException::withMessages(['status' => 'Pengajuan ini sudah diproses.']);
            }

            $p->status = Peminjaman::STATUS_DITOLAK;
            $p->catatan_admin = $catatan;
            $p->save();

            AktivitasLog::catat('tolak_peminjaman', $p->id, $p->lahan_id,
                ['status' => Peminjaman::STATUS_MENUNGGU],
                ['status' => $p->status, 'catatan_admin' => $catatan]
            );

            return $p;
        });
    }
}
