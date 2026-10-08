<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class AktivitasLog extends Model
{
    protected $table = 'aktivitas_logs';

    protected $fillable = ['user_id', 'peminjaman_id', 'lahan_id', 'aksi', 'data_lama', 'data_baru'];

    protected function casts(): array
    {
        return [
            'data_lama' => 'array',
            'data_baru' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // user_id null = aksi sistem (scheduler).
    public static function catat(
        string $aksi,
        ?int $peminjamanId = null,
        ?int $lahanId = null,
        ?array $lama = null,
        ?array $baru = null,
    ): self {
        return self::create([
            'user_id' => Auth::id(),
            'peminjaman_id' => $peminjamanId,
            'lahan_id' => $lahanId,
            'aksi' => $aksi,
            'data_lama' => $lama,
            'data_baru' => $baru,
        ]);
    }
}
