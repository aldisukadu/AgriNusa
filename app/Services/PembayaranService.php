<?php

namespace App\Services;

use App\Models\AktivitasLog;
use App\Models\Peminjaman;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class PembayaranService
{
    public function lapor(Peminjaman $peminjaman, string $metode, ?UploadedFile $bukti): Peminjaman
    {
        $path = $bukti?->store('bukti-bayar', 'local');

        try {
            return DB::transaction(function () use ($peminjaman, $metode, $path) {
                $p = $this->kunci($peminjaman->id);

                if ($p->status !== Peminjaman::STATUS_DISETUJUI || $p->status_deposit !== Peminjaman::DEPOSIT_BELUM_DIBAYAR) {
                    $this->gagal('pembayaran', 'Pembayaran tidak dapat dilaporkan pada status ini.');
                }

                if ($p->tanggal_bayar !== null) {
                    $this->gagal('pembayaran', 'Pembayaran sudah dilaporkan. Tunggu konfirmasi admin.');
                }

                if ($p->batas_bayar !== null && now()->gt($p->batas_bayar)) {
                    $this->gagal('pembayaran', 'Batas pembayaran sudah lewat.');
                }

                $p->metode_bayar = $metode;
                $p->bukti_bayar = $path;
                $p->tanggal_bayar = now();
                $p->save();

                AktivitasLog::catat('lapor_pembayaran', $p->id, $p->lahan_id, null, [
                    'metode_bayar' => $metode,
                    'ada_bukti' => $path !== null,
                ]);

                return $p;
            });
        } catch (Throwable $e) {
            $this->hapusFile($path);
            throw $e;
        }
    }

    public function konfirmasi(Peminjaman $peminjaman, ?string $metode = null): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman, $metode) {
            $p = $this->kunci($peminjaman->id);

            if ($p->status !== Peminjaman::STATUS_DISETUJUI || $p->status_deposit !== Peminjaman::DEPOSIT_BELUM_DIBAYAR) {
                $this->gagal('pembayaran', 'Pembayaran tidak dapat dikonfirmasi pada status ini.');
            }

            if ($p->tanggal_bayar === null) {
                if (! in_array($metode, ['tunai', 'transfer'], true)) {
                    $this->gagal('metode_bayar', 'Pilih metode pembayaran.');
                }
                $p->metode_bayar = $metode;
                $p->tanggal_bayar = now();
            }

            $p->status_deposit = Peminjaman::DEPOSIT_DIBAYAR;
            $p->status = Peminjaman::STATUS_AKTIF;
            $p->aktif_sejak = now();
            $p->save();

            AktivitasLog::catat('konfirmasi_pembayaran', $p->id, $p->lahan_id,
                ['status' => Peminjaman::STATUS_DISETUJUI, 'status_deposit' => Peminjaman::DEPOSIT_BELUM_DIBAYAR],
                ['status' => $p->status, 'status_deposit' => $p->status_deposit, 'metode_bayar' => $p->metode_bayar]
            );

            return $p;
        });
    }

    public function tolak(Peminjaman $peminjaman, string $alasan): Peminjaman
    {
        $fileLama = null;

        $hasil = DB::transaction(function () use ($peminjaman, $alasan, &$fileLama) {
            $p = $this->kunci($peminjaman->id);

            if ($p->status !== Peminjaman::STATUS_DISETUJUI || $p->tanggal_bayar === null) {
                $this->gagal('pembayaran', 'Tidak ada laporan pembayaran yang bisa ditolak.');
            }

            $fileLama = $p->bukti_bayar;

            $p->metode_bayar = null;
            $p->bukti_bayar = null;
            $p->tanggal_bayar = null;
            $p->catatan_admin = 'Pembayaran ditolak: '.$alasan;

            $perpanjang = now()->addHours(config('greenhouse.tolak_bayar_perpanjang_jam'));
            if ($p->batas_bayar === null || $perpanjang->gt($p->batas_bayar)) {
                $p->batas_bayar = $perpanjang;
            }
            $p->save();

            AktivitasLog::catat('tolak_pembayaran', $p->id, $p->lahan_id, null,
                ['alasan' => $alasan, 'batas_bayar' => $p->batas_bayar->toDateTimeString()]
            );

            return $p;
        });

        $this->hapusFile($fileLama);

        return $hasil;
    }

    private function kunci(int $id): Peminjaman
    {
        return Peminjaman::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    private function gagal(string $kunci, string $pesan): never
    {
        throw ValidationException::withMessages([$kunci => $pesan]);
    }

    private function hapusFile(?string $path): void
    {
        if ($path) {
            Storage::disk('local')->delete($path);
        }
    }
}
