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

class PerawatanService
{
    public function catatPeminjam(Peminjaman $peminjaman, User $user, array $data, ?UploadedFile $foto): PerawatanLog
    {
        $path = $foto?->store('perawatan', 'public');

        try {
            return DB::transaction(function () use ($peminjaman, $user, $data, $path) {
                $p = $this->kunci($peminjaman->id);

                if ($p->status !== Peminjaman::STATUS_AKTIF) {
                    $this->gagal('status', 'Perawatan hanya bisa dicatat saat peminjaman aktif.');
                }

                $log = $this->buatLog($p, $user, PerawatanLog::PELAKSANA_PEMINJAM, $data, 0, $path);

                if (in_array($p->status_perawatan, [Peminjaman::RAWAT_PERINGATAN, Peminjaman::RAWAT_TERABAIKAN], true)) {
                    $lama = $p->status_perawatan;
                    $p->status_perawatan = Peminjaman::RAWAT_NORMAL;
                    $p->save();

                    AktivitasLog::catat('perawatan_pulih', $p->id, $p->lahan_id,
                        ['status_perawatan' => $lama],
                        ['status_perawatan' => $p->status_perawatan]
                    );
                }

                return $log;
            });
        } catch (Throwable $e) {
            $this->hapusFile($path);
            throw $e;
        }
    }

    public function ambilAlih(Peminjaman $peminjaman): Peminjaman
    {
        return DB::transaction(function () use ($peminjaman) {
            $p = $this->kunci($peminjaman->id);

            if ($p->status !== Peminjaman::STATUS_AKTIF) {
                $this->gagal('status', 'Peminjaman tidak aktif.');
            }

            if ($p->status_perawatan !== Peminjaman::RAWAT_TERABAIKAN) {
                $this->gagal('status_perawatan', 'Lahan belum berstatus terabaikan, admin belum boleh mengambil alih.');
            }

            $p->status_perawatan = Peminjaman::RAWAT_DIAMBIL_ALIH;
            $p->save();

            AktivitasLog::catat('ambil_alih_perawatan', $p->id, $p->lahan_id,
                ['status_perawatan' => Peminjaman::RAWAT_TERABAIKAN],
                ['status_perawatan' => $p->status_perawatan]
            );

            return $p;
        });
    }

    public function catatAdmin(Peminjaman $peminjaman, User $admin, array $data, ?UploadedFile $foto): PerawatanLog
    {
        $path = $foto?->store('perawatan', 'public');

        try {
            return DB::transaction(function () use ($peminjaman, $admin, $data, $path) {
                $p = $this->kunci($peminjaman->id);

                if ($p->status !== Peminjaman::STATUS_AKTIF) {
                    $this->gagal('status', 'Peminjaman tidak aktif.');
                }

                if ($p->status_perawatan !== Peminjaman::RAWAT_DIAMBIL_ALIH) {
                    $this->gagal('status_perawatan', 'Ambil alih perawatan terlebih dahulu.');
                }

                $log = $this->buatLog($p, $admin, PerawatanLog::PELAKSANA_ADMIN, $data, (int) $data['biaya'], $path);
                $this->hitungUlangBiaya($p);

                AktivitasLog::catat('catat_perawatan_admin', $p->id, $p->lahan_id, null, [
                    'kegiatan' => $log->kegiatan,
                    'biaya' => $log->biaya,
                    'total_biaya_perawatan' => $p->total_biaya_perawatan,
                ]);

                return $log;
            });
        } catch (Throwable $e) {
            $this->hapusFile($path);
            throw $e;
        }
    }

    public function hapusCatatanAdmin(PerawatanLog $perawatanLog): void
    {
        $foto = DB::transaction(function () use ($perawatanLog) {
            $p = $this->kunci($perawatanLog->peminjaman_id);
            $log = PerawatanLog::whereKey($perawatanLog->id)->lockForUpdate()->firstOrFail();

            if ($log->pelaksana !== PerawatanLog::PELAKSANA_ADMIN) {
                $this->gagal('hapus', 'Catatan peminjam tidak dapat dihapus.');
            }

            if ($p->status !== Peminjaman::STATUS_AKTIF) {
                $this->gagal('hapus', 'Catatan hanya bisa dihapus selama peminjaman aktif.');
            }

            $lama = $log->only(['tanggal', 'kegiatan', 'biaya', 'catatan']);
            $foto = $log->foto;
            $log->delete();
            $this->hitungUlangBiaya($p);

            AktivitasLog::catat('hapus_perawatan_admin', $p->id, $p->lahan_id, $lama, [
                'total_biaya_perawatan' => $p->total_biaya_perawatan,
            ]);

            return $foto;
        });

        if ($foto) {
            Storage::disk('public')->delete($foto);
        }
    }

    private function buatLog(Peminjaman $p, User $user, string $pelaksana, array $data, int $biaya, ?string $foto): PerawatanLog
    {
        $log = new PerawatanLog([
            'peminjaman_id' => $p->id,
            'tanggal' => $data['tanggal'],
            'kegiatan' => $data['kegiatan'],
            'catatan' => $data['catatan'] ?? null,
            'foto' => $foto,
        ]);
        $log->user_id = $user->id;
        $log->pelaksana = $pelaksana;
        $log->biaya = $biaya;
        $log->save();

        return $log;
    }

    private function hitungUlangBiaya(Peminjaman $p): void
    {
        $p->total_biaya_perawatan = (int) $p->perawatanLogs()->sum('biaya');
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

    private function hapusFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
