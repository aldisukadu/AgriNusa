<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerawatanLog extends Model
{
    protected $table = 'perawatan_logs';

    public const PELAKSANA_PEMINJAM = 'peminjam';
    public const PELAKSANA_ADMIN = 'admin';

    public const KEGIATAN = ['menyiram', 'pemupukan', 'penyiangan', 'pengendalian_hama', 'lainnya'];

    // pelaksana, user_id, dan biaya diisi controller, bukan dari input form.
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
}
