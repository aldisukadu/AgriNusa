<?php

namespace App\Services;

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
                $p = Peminjaman::whereKey($peminjaman->id)->lockForUpdate()->firstOrFail();

                if ($p->status !== Peminjaman::STATUS_AKTIF) {
                    throw ValidationException::withMessages([
                        'status' => 'Perawatan hanya bisa dicatat saat peminjaman aktif.',
                    ]);
                }

                return PerawatanLog::buat($p, $user, PerawatanLog::PELAKSANA_PEMINJAM, $data['kegiatan'], $data, 0, $path);
            });
        } catch (Throwable $e) {
            if ($path) {
                Storage::disk('public')->delete($path);
            }
            throw $e;
        }
    }
}
