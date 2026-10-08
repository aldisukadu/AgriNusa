<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerawatanLog extends Model
{
    protected $table = 'perawatan_logs';

    public const PELAKSANA_PEMINJAM = 'peminjam';
    public const PELAKSANA_ADMIN = 'admin';

    // Pilihan kegiatan perawatan oleh peminjam. Pembersihan hanya dicatat admin.
    public const KEGIATAN = ['menyiram', 'pemupukan', 'penyiangan', 'pengendalian_hama', 'lainnya'];
    public const KEGIATAN_PEMBERSIHAN = 'pembersihan';

    // pelaksana, user_id, dan biaya diisi lewat buat(), bukan dari input form.
    protected $fillable = ['peminjaman_id', 'tanggal', 'kegiatan', 'catatan', 'foto'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'date',
            'biaya' => 'integer',
        ];
    }

    public function peminjaman(): BelongsTo
    {
        return $this->belongsTo(Peminjaman::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function buat(
        Peminjaman $peminjaman,
        User $user,
        string $pelaksana,
        string $kegiatan,
        array $data,
        int $biaya,
        ?string $foto
    ): self {
        $log = new self([
            'peminjaman_id' => $peminjaman->id,
            'tanggal' => $data['tanggal'],
            'kegiatan' => $kegiatan,
            'catatan' => $data['catatan'] ?? null,
            'foto' => $foto,
        ]);
        $log->user_id = $user->id;
        $log->pelaksana = $pelaksana;
        $log->biaya = $biaya;
        $log->save();

        return $log;
    }
}
