<?php

namespace App\Services;

use App\Models\AktivitasLog;
use App\Models\PerawatanLog;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class PengembalianService
{
    public function kembalikan(Peminjaman $peminjaman, array $data, UploadedFile $foto): Peminjaman
    {
        $path = $foto->store('kembali', 'public');

        try {
            return DB::transaction(function () use ($peminjaman, $data, $path) {
                $p = $this->kunci($peminjaman->id);

                if ($p->status !== Peminjaman::STATUS_AKTIF) {
                    $this->gagal('status', 'Hanya peminjaman aktif yang dapat dikembalikan.');
                }

                $p->status = Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN;
                $p->tanggal_kembali = today();
                $p->kondisi_kembali = $data['kondisi_kembali'];
                $p->foto_kembali = $path;
                $p->save();

                AktivitasLog::catat('kembalikan_lahan', $p->id, $p->lahan_id,
                    ['status' => Peminjaman::STATUS_AKTIF],
                    ['status' => $p->status, 'tanggal_kembali' => $p->tanggal_kembali->toDateString()]
                );

                return $p;
            });
        } catch (Throwable $e) {
            Storage::disk('public')->delete($path);
            throw $e;
        }
    }

    public function kembalikanOlehAdmin(Peminjaman $peminjaman): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman) {
            $p = $this->kunci($peminjaman->id);

            if ($p->status !== Peminjaman::STATUS_AKTIF) {
                $this->gagal('status', 'Hanya peminjaman aktif yang dapat ditandai dikembalikan.');
            }

            $batas = $p->tanggal_selesai->copy()->addDays((int) config('greenhouse.batas_kembali_hari'));

            if (! today()->gt($batas)) {
                $this->gagal('status', 'Lahan baru bisa ditandai dikembalikan oleh admin setelah '.$batas->format('d/m/Y').'.');
            }

            $p->status = Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN;
            $p->tanggal_kembali = today();
            $p->kondisi_kembali = 'Ditandai oleh admin: peminjam tidak mengembalikan lahan.';
            $p->save();

            AktivitasLog::catat('kembalikan_oleh_admin', $p->id, $p->lahan_id,
                ['status' => Peminjaman::STATUS_AKTIF],
                ['status' => $p->status]
            );

            return $p;
        });
    }

    public function catatPembersihan(Peminjaman $peminjaman, User $admin, array $data, ?UploadedFile $foto): PerawatanLog
    {
        $path = $foto?->store('perawatan', 'public');

        try {
            return DB::transaction(function () use ($peminjaman, $admin, $data, $path) {
                $p = $this->kunci($peminjaman->id);

                if ($p->status !== Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN) {
                    $this->gagal('status', 'Pembersihan hanya bisa dicatat saat peminjaman menunggu pemeriksaan.');
                }

                $log = PerawatanLog::buat(
                    $p, $admin, PerawatanLog::PELAKSANA_ADMIN, PerawatanLog::KEGIATAN_PEMBERSIHAN,
                    $data, (int) $data['biaya'], $path
                );
                $this->hitungUlang($p);

                AktivitasLog::catat('catat_pembersihan', $p->id, $p->lahan_id, null, [
                    'biaya' => $log->biaya,
                    'total_biaya_pembersihan' => $p->total_biaya_pembersihan,
                ]);

                return $log;
            });
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }

    public function hapusPembersihan(PerawatanLog $perawatanLog): void
    {
        $foto = DB::transaction(function () use ($perawatanLog) {
            $p = $this->kunci($perawatanLog->peminjaman_id);
            $log = PerawatanLog::whereKey($perawatanLog->id)->lockForUpdate()->firstOrFail();

            if ($log->pelaksana !== PerawatanLog::PELAKSANA_ADMIN) {
                $this->gagal('hapus', 'Catatan peminjam tidak dapat dihapus.');
            }

            if ($p->status !== Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN) {
                $this->gagal('hapus', 'Catatan hanya bisa dihapus sebelum deposit diselesaikan.');
            }

            $lama = $log->only(['tanggal', 'kegiatan', 'biaya', 'catatan']);
            $foto = $log->foto;
            $log->delete();
            $this->hitungUlang($p);

            AktivitasLog::catat('hapus_pembersihan', $p->id, $p->lahan_id, $lama, [
                'total_biaya_pembersihan' => $p->total_biaya_pembersihan,
            ]);

            return $foto;
        });

        if ($foto) {
            Storage::disk('public')->delete($foto);
        }
    }

    public function selesaikan(Peminjaman $peminjaman): array
    {
        return DB::transaction(function () use ($peminjaman) {
            $p = $this->kunci($peminjaman->id);

            if ($p->status !== Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN) {
                $this->gagal('status', 'Peminjaman belum menunggu pemeriksaan.');
            }

            if ($p->status_deposit !== Peminjaman::DEPOSIT_DIBAYAR) {
                $this->gagal('status_deposit', 'Deposit belum dibayar.');
            }

            $this->hitungUlang($p);
            $hasil = $p->hitungPenyelesaianDeposit();

            $p->status_deposit = $hasil['status_deposit'];
            $p->kekurangan_bayar = $hasil['kekurangan'];
            $p->tanggal_deposit_selesai = now();
            $p->status = Peminjaman::STATUS_DIKEMBALIKAN;
            $p->save();

            AktivitasLog::catat('selesaikan_deposit', $p->id, $p->lahan_id,
                ['status' => Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN, 'status_deposit' => Peminjaman::DEPOSIT_DIBAYAR],
                [
                    'status' => $p->status,
                    'status_deposit' => $p->status_deposit,
                    'total_biaya_pembersihan' => $p->total_biaya_pembersihan,
                    'sisa' => $hasil['sisa'],
                    'kekurangan' => $hasil['kekurangan'],
                ]
            );

            return $hasil;
        });
    }

    private function hitungUlang(Peminjaman $p): void
    {
        $p->total_biaya_pembersihan = (int) $p->perawatanLogs()
            ->where('pelaksana', PerawatanLog::PELAKSANA_ADMIN)
            ->sum('biaya');
        $p->save();
    }

    private function kunci(int $id): Peminjaman
    {
        return Peminjaman::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    private function gagal(string $kunci, string $pesan): never
    {
        throw ValidationException::withMessages([$kunci => $pesan]);
    }
}
