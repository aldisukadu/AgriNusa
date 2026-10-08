<?php

namespace Tests\Feature;

use App\Models\AktivitasLog;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class JadwalTest extends TestCase
{
    use RefreshDatabase, MembuatData;

    private function disetujui(array $ubah = []): Peminjaman
    {
        $p = $this->buatPeminjaman($this->buatUser(), $this->buatLahan(), 'disetujui', $this->hari(5), $this->hari(10));
        $p->forceFill(array_merge(['batas_bayar' => now()->subMinute()], $ubah))->save();

        return $p;
    }

    private function aktif(int $selesaiHari): Peminjaman
    {
        $p = $this->buatPeminjaman($this->buatUser(), $this->buatLahan(), 'aktif', $this->hari(-20), $this->hari($selesaiHari));
        $p->forceFill([
            'status_deposit' => 'dibayar',
            'metode_bayar' => 'tunai',
            'tanggal_bayar' => now()->subDays(21),
            'aktif_sejak' => now()->subDays(20),
        ])->save();

        return $p;
    }

    private function jalankan(): void
    {
        $this->artisan('greenhouse:proses-jadwal')->assertExitCode(0);
    }

    // ---------- pembatalan pembayaran ----------
    public function test_pengajuan_lewat_batas_tanpa_laporan_dibatalkan(): void
    {
        $p = $this->disetujui();

        $this->jalankan();

        $p->refresh();
        $this->assertSame(Peminjaman::STATUS_DIBATALKAN, $p->status);
        $this->assertSame('belum_dibayar', $p->status_deposit);
        $this->assertStringContainsString('Dibatalkan otomatis', $p->catatan_admin);
    }

    public function test_belum_lewat_batas_tidak_disentuh(): void
    {
        $p = $this->disetujui(['batas_bayar' => now()->addMinute()]);

        $this->jalankan();

        $this->assertSame('disetujui', $p->fresh()->status);
    }

    public function test_sudah_melapor_tidak_dibatalkan_walau_lewat_batas(): void
    {
        $p = $this->disetujui(['tanggal_bayar' => now()->subMinutes(5), 'metode_bayar' => 'transfer']);

        $this->jalankan();

        $this->assertSame('disetujui', $p->fresh()->status);
    }

    public function test_pengajuan_menunggu_tidak_disentuh(): void
    {
        $p = $this->buatPeminjaman($this->buatUser(), $this->buatLahan(), 'menunggu', $this->hari(-5), $this->hari(-1));

        $this->jalankan();

        $this->assertSame('menunggu', $p->fresh()->status);
    }

    public function test_jadwal_terbuka_setelah_pembatalan(): void
    {
        $lahan = $this->buatLahan();
        $p = $this->buatPeminjaman($this->buatUser(), $lahan, 'disetujui', $this->hari(5), $this->hari(10));
        $p->forceFill(['batas_bayar' => now()->subMinute()])->save();

        $payload = [
            'lahan_id' => $lahan->id,
            'tanggal_mulai' => $this->hari(6),
            'tanggal_selesai' => $this->hari(8),
            'jenis_tanaman' => 'Selada',
            'tujuan' => 'praktikum',
        ];

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.store'), $payload)
            ->assertSessionHasErrors('tanggal_mulai');

        $this->jalankan();

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.store'), $payload)
            ->assertSessionHasNoErrors();
    }

    // ---------- pengembalian otomatis ----------
    public function test_lahan_lewat_batas_kembali_ditandai_menunggu_pemeriksaan(): void
    {
        $n = (int) config('greenhouse.batas_kembali_hari');
        $p = $this->aktif(-($n + 1));

        $this->jalankan();

        $p->refresh();
        $this->assertSame(Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN, $p->status);
        $this->assertSame(today()->toDateString(), $p->tanggal_kembali->toDateString());
        $this->assertStringContainsString('otomatis', $p->kondisi_kembali);
    }

    public function test_tepat_di_batas_kembali_belum_ditandai(): void
    {
        $n = (int) config('greenhouse.batas_kembali_hari');
        $p = $this->aktif(-$n);

        $this->jalankan();

        $this->assertSame('aktif', $p->fresh()->status);
    }

    public function test_status_selain_aktif_tidak_disentuh(): void
    {
        $p = $this->aktif(-10);
        $p->forceFill(['status' => Peminjaman::STATUS_MENUNGGU_PEMERIKSAAN])->save();

        $this->jalankan();

        $this->assertSame('menunggu_pemeriksaan', $p->fresh()->status);
        $this->assertDatabaseMissing('aktivitas_logs', ['aksi' => 'kembalikan_otomatis']);
    }

    // ---------- keamanan menjalankan ulang ----------
    public function test_dijalankan_dua_kali_hasilnya_sama(): void
    {
        $batal = $this->disetujui();
        $kembali = $this->aktif(-10);

        $this->jalankan();
        $this->jalankan();

        $this->assertSame(1, AktivitasLog::where('aksi', 'batalkan_otomatis')->count());
        $this->assertSame(1, AktivitasLog::where('aksi', 'kembalikan_otomatis')->count());
        $this->assertSame('dibatalkan', $batal->fresh()->status);
        $this->assertSame('menunggu_pemeriksaan', $kembali->fresh()->status);
    }

    public function test_perubahan_otomatis_dicatat_tanpa_pengguna(): void
    {
        $p = $this->disetujui();

        $this->jalankan();

        $log = AktivitasLog::where('aksi', 'batalkan_otomatis')->firstOrFail();
        $this->assertNull($log->user_id);
        $this->assertSame($p->id, $log->peminjaman_id);
    }

    public function test_perintah_menampilkan_jumlah_yang_diproses(): void
    {
        $this->disetujui();
        $this->aktif(-10);

        $this->artisan('greenhouse:proses-jadwal')
            ->expectsOutput('Dibatalkan: 1. Ditandai dikembalikan: 1.')
            ->assertExitCode(0);
    }
}
