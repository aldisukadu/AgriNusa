<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Peminjaman extends Model
{
    protected $table = 'peminjamans';

    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_MENUNGGU_PEMERIKSAAN = 'menunggu_pemeriksaan';
    public const STATUS_DIKEMBALIKAN = 'dikembalikan';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    // Status yang mengunci jadwal ('menunggu' tidak).
    public const STATUS_MEMBLOKIR = [
        self::STATUS_DISETUJUI,
        self::STATUS_AKTIF,
        self::STATUS_MENUNGGU_PEMERIKSAAN,
    ];

    public const DEPOSIT_BELUM_DIBAYAR = 'belum_dibayar';
    public const DEPOSIT_DIBAYAR = 'dibayar';
    public const DEPOSIT_DIKEMBALIKAN = 'dikembalikan';
    public const DEPOSIT_DIPOTONG = 'dipotong';
    public const DEPOSIT_TERPAKAI_HABIS = 'terpakai_habis';

    public const RAWAT_NORMAL = 'normal';
    public const RAWAT_PERINGATAN = 'peringatan';
    public const RAWAT_TERABAIKAN = 'terabaikan';
    public const RAWAT_DIAMBIL_ALIH = 'diambil_alih';

    // Hanya kolom form pengajuan. Kolom lain diisi eksplisit di controller.
    protected $fillable = [
        'user_id',
        'lahan_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'jenis_tanaman',
        'tujuan',
        'nominal_deposit',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'tanggal_kembali' => 'date',
            'batas_bayar' => 'datetime',
            'tanggal_bayar' => 'datetime',
            'aktif_sejak' => 'datetime',
            'peringatan_sejak' => 'datetime',
            'tanggal_deposit_selesai' => 'datetime',
            'nominal_deposit' => 'integer',
            'total_biaya_perawatan' => 'integer',
            'kekurangan_bayar' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lahan(): BelongsTo
    {
        return $this->belongsTo(Lahan::class);
    }

    public function perawatanLogs(): HasMany
    {
        return $this->hasMany(PerawatanLog::class);
    }

    public function scopeMemblokir(Builder $query): Builder
    {
        return $query->whereIn('status', self::STATUS_MEMBLOKIR);
    }

    // Tanggal inklusif. Wajib dipanggil di dalam transaksi dengan baris lahan terkunci.
    public function scopeBentrok(Builder $query, int $lahanId, $mulai, $selesai, ?int $kecualiId = null): Builder
    {
        return $query->memblokir()
            ->where('lahan_id', $lahanId)
            ->where('tanggal_mulai', '<=', $selesai)
            ->where('tanggal_selesai', '>=', $mulai)
            ->when($kecualiId, fn (Builder $q) => $q->where('id', '!=', $kecualiId));
    }

    protected function saldoDeposit(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->nominal_deposit - $this->total_biaya_perawatan));
    }

    protected function kekuranganBiaya(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->total_biaya_perawatan - $this->nominal_deposit));
    }

    // biaya 0 -> dikembalikan; biaya < deposit -> dipotong; biaya >= deposit -> terpakai_habis.
    public function hitungPenyelesaianDeposit(): array
    {
        $biaya = $this->total_biaya_perawatan;
        $deposit = $this->nominal_deposit;

        if ($biaya === 0) {
            return ['status_deposit' => self::DEPOSIT_DIKEMBALIKAN, 'sisa' => $deposit, 'kekurangan' => 0];
        }

        if ($biaya < $deposit) {
            return ['status_deposit' => self::DEPOSIT_DIPOTONG, 'sisa' => $deposit - $biaya, 'kekurangan' => 0];
        }

        return ['status_deposit' => self::DEPOSIT_TERPAKAI_HABIS, 'sisa' => 0, 'kekurangan' => $biaya - $deposit];
    }
}
