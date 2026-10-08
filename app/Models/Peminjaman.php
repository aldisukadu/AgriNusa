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

    // Status peminjaman
    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DISETUJUI = 'disetujui';
    public const STATUS_AKTIF = 'aktif';
    public const STATUS_DITOLAK = 'ditolak';
    public const STATUS_MENUNGGU_PEMERIKSAAN = 'menunggu_pemeriksaan';
    public const STATUS_DIKEMBALIKAN = 'dikembalikan';
    public const STATUS_DIBATALKAN = 'dibatalkan';

    // Hanya status ini yang mengunci jadwal lahan.
    // 'menunggu' TIDAK mengunci (keputusan desain: beberapa pengajuan boleh antre untuk periode sama;
    // bentrok dicek ulang saat admin menyetujui).
    public const STATUS_MEMBLOKIR = [
        self::STATUS_DISETUJUI,
        self::STATUS_AKTIF,
        self::STATUS_MENUNGGU_PEMERIKSAAN,
    ];

    // Status deposit
    public const DEPOSIT_BELUM_DIBAYAR = 'belum_dibayar';
    public const DEPOSIT_DIBAYAR = 'dibayar';
    public const DEPOSIT_DIKEMBALIKAN = 'dikembalikan';
    public const DEPOSIT_DIPOTONG = 'dipotong';
    public const DEPOSIT_TERPAKAI_HABIS = 'terpakai_habis';

    // Status perawatan
    public const RAWAT_NORMAL = 'normal';
    public const RAWAT_PERINGATAN = 'peringatan';
    public const RAWAT_TERABAIKAN = 'terabaikan';
    public const RAWAT_DIAMBIL_ALIH = 'diambil_alih';

    // Hanya kolom yang aman diisi dari form pengajuan peminjam.
    // Status, deposit, biaya, dll. diubah lewat penugasan eksplisit di controller/service,
    // JANGAN lewat $request->all().
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

    // ---------- Relasi ----------
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

    // ---------- Scope ----------
    public function scopeMemblokir(Builder $query): Builder
    {
        return $query->whereIn('status', self::STATUS_MEMBLOKIR);
    }

    /**
     * Peminjaman yang bentrok dengan rentang [$mulai, $selesai] pada lahan tertentu.
     * Tanggal bersifat INKLUSIF: selesai tgl 10 dan mulai tgl 10 dianggap bentrok.
     * Pemanggil WAJIB berada di dalam DB::transaction dan sudah mengunci baris lahan
     * (lihat tahap 3), kalau tidak, dua permintaan bersamaan bisa lolos.
     */
    public function scopeBentrok(Builder $query, int $lahanId, $mulai, $selesai, ?int $kecualiId = null): Builder
    {
        return $query->memblokir()
            ->where('lahan_id', $lahanId)
            ->where('tanggal_mulai', '<=', $selesai)
            ->where('tanggal_selesai', '>=', $mulai)
            ->when($kecualiId, fn (Builder $q) => $q->where('id', '!=', $kecualiId));
    }

    // ---------- Turunan deposit ----------
    protected function saldoDeposit(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->nominal_deposit - $this->total_biaya_perawatan));
    }

    protected function kekuranganBiaya(): Attribute
    {
        return Attribute::get(fn () => max(0, $this->total_biaya_perawatan - $this->nominal_deposit));
    }

    /**
     * Hasil penyelesaian deposit berdasarkan total biaya perawatan saat ini.
     * Aturan:
     *  - biaya = 0                -> dikembalikan (sisa = seluruh deposit)
     *  - 0 < biaya < deposit      -> dipotong (sisa dikembalikan)
     *  - biaya >= deposit         -> terpakai_habis (selisih masuk kekurangan_bayar)
     * Pemanggil bertanggung jawab memastikan total_biaya_perawatan sudah
     * dihitung ulang dari SUM(perawatan_logs.biaya).
     */
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
