<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lahan extends Model
{
    protected $table = 'lahans';

    public const STATUS_TERSEDIA = 'tersedia';
    public const STATUS_PERAWATAN = 'perawatan';

    protected $fillable = ['green_house_id', 'kode', 'luas', 'media_tanam', 'deposit', 'status'];

    protected function casts(): array
    {
        return [
            'luas' => 'decimal:2',
            'deposit' => 'integer',
        ];
    }

    public function greenHouse(): BelongsTo
    {
        return $this->belongsTo(GreenHouse::class);
    }

    public function peminjamans(): HasMany
    {
        return $this->hasMany(Peminjaman::class);
    }
}
