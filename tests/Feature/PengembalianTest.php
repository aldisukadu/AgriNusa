<?php

namespace Tests\Feature;

use App\Models\PerawatanLog;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class PengembalianTest extends TestCase
{
    use RefreshDatabase, MembuatData;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function aktif(int $mulai = -10, int $selesai = 20, $lahan = null): Peminjaman
    {
        $p = $this->buatPeminjaman($this->buatUser(), $lahan ?? $this->buatLahan(), 'aktif', $this->hari($mulai), $this->hari($selesai));
        $p->forceFill([
            'status_deposit' => 'dibayar',
            'metode_bayar' => 'tunai',
            'tanggal_bayar' => now()->subDays(11),
            'aktif_sejak' => now()->subDays(10),
        ])->save();

        return $p;
    }

    private function periksa($lahan = null): Peminjaman
    {
        $p = $this->aktif(-10, 20, $lahan);
        $p->forceFill([
            'status' => 'menunggu_pemeriksaan',
            'tanggal_kembali' => today(),
            'kondisi_kembali' => 'Sudah dibersihkan',
        ])->save();

        return $p;
    }

    private function logAdmin(Peminjaman $p, int $biaya): PerawatanLog
    {
        return PerawatanLog::forceCreate([
            'peminjaman_id' => $p->id,
            'user_id' => $this->buatUser('admin')->id,
            'pelaksana' => 'admin',
            'tanggal' => now()->toDateString(),
            'kegiatan' => 'pembersihan',
            'biaya' => $biaya,
            'catatan' => 'Bersih-bersih',
        ]);
    }

    private function dataPembersihan(array $ubah = []): array
    {
        return array_merge([
            'tanggal' => now()->toDateString(),
            'biaya' => 25000,
            'catatan' => 'Mencabut sisa tanaman dan membuang media',
        ], $ubah);
    }

    private function foto(): UploadedFile
    {
        return UploadedFile::fake()->create('kondisi.jpg', 100, 'image/jpeg');
    }

    // ---------- peminjam mengembalikan ----------
    public function test_pengembalian_wajib_foto_dan_mengubah_status(): void
    {
        $p = $this->aktif();
        $url = route('peminjam.peminjamans.kembali', $p);

        $this->actingAs($p->user)->post($url, ['kondisi_kembali' => 'Bersih'])->assertSessionHasErrors('foto_kembali');
        $this->assertSame('aktif', $p->fresh()->status);

        $this->actingAs($p->user)->post($url, ['kondisi_kembali' => 'Bersih', 'foto_kembali' => $this->foto()])
            ->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertSame('menunggu_pemeriksaan', $p->status);
        $this->assertSame(today()->toDateString(), $p->tanggal_kembali->toDateString());
        $this->assertSame('Bersih', $p->kondisi_kembali);
        Storage::disk('public')->assertExists($p->foto_kembali);
    }

    public function test_pengembalian_wajib_kondisi(): void
    {
        $p = $this->aktif();

        $this->actingAs($p->user)
            ->post(route('peminjam.peminjamans.kembali', $p), ['foto_kembali' => $this->foto()])
            ->assertSessionHasErrors('kondisi_kembali');
    }

    public function test_hanya_peminjaman_aktif_yang_bisa_dikembalikan(): void
    {
        $p = $this->buatPeminjaman($this->buatUser(), $this->buatLahan(), 'disetujui', $this->hari(1), $this->hari(10));

        $this->actingAs($p->user)
            ->post(route('peminjam.peminjamans.kembali', $p), ['kondisi_kembali' => 'x', 'foto_kembali' => $this->foto()])
            ->assertSessionHasErrors('status');

        $this->assertSame('disetujui', $p->fresh()->status);
        $this->assertNull($p->fresh()->foto_kembali);
    }

    public function test_peminjam_lain_tidak_bisa_mengembalikan(): void
    {
        $p = $this->aktif();

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.kembali', $p), ['kondisi_kembali' => 'x', 'foto_kembali' => $this->foto()])
            ->assertForbidden();
    }

    // ---------- admin menandai dikembalikan ----------
    public function test_admin_menandai_dikembalikan_hanya_setelah_batas(): void
    {
        $admin = $this->buatUser('admin');

        $belum = $this->aktif(-20, -2);
        $this->actingAs($admin)->post(route('admin.peminjamans.kembalikan', $belum))->assertSessionHasErrors('status');
        $this->assertSame('aktif', $belum->fresh()->status);

        $lewat = $this->aktif(-20, -5);
        $this->actingAs($admin)->post(route('admin.peminjamans.kembalikan', $lewat))->assertSessionHasNoErrors();

        $lewat->refresh();
        $this->assertSame('menunggu_pemeriksaan', $lewat->status);
        $this->assertStringContainsString('admin', $lewat->kondisi_kembali);
    }

    // ---------- pemeriksaan dan pembersihan ----------
    public function test_pembersihan_hanya_saat_menunggu_pemeriksaan(): void
    {
        $p = $this->aktif();

        $this->actingAs($this->buatUser('admin'))
            ->post(route('admin.peminjamans.pembersihan.store', $p), $this->dataPembersihan())
            ->assertSessionHasErrors('status');

        $this->assertSame(0, PerawatanLog::count());
    }

    public function test_pembersihan_dijumlahkan_dan_catatan_wajib(): void
    {
        $p = $this->periksa();
        $admin = $this->buatUser('admin');
        $url = route('admin.peminjamans.pembersihan.store', $p);

        $this->actingAs($admin)->post($url, $this->dataPembersihan(['catatan' => '']))->assertSessionHasErrors('catatan');

        $this->actingAs($admin)->post($url, $this->dataPembersihan(['biaya' => 25000]))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post($url, $this->dataPembersihan(['biaya' => 15000]))->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertSame(40000, $p->total_biaya_pembersihan);
        $this->assertSame(60000, $p->saldo_deposit);
        $this->assertSame(2, PerawatanLog::where('kegiatan', 'pembersihan')->where('pelaksana', 'admin')->count());
    }

    public function test_hapus_pembersihan_menghitung_ulang_total(): void
    {
        $p = $this->periksa();
        $admin = $this->buatUser('admin');
        $url = route('admin.peminjamans.pembersihan.store', $p);

        $this->actingAs($admin)->post($url, $this->dataPembersihan(['biaya' => 25000]));
        $this->actingAs($admin)->post($url, $this->dataPembersihan(['biaya' => 15000]));

        $pertama = PerawatanLog::where('biaya', 25000)->firstOrFail();
        $this->actingAs($admin)->delete(route('admin.pembersihan.destroy', $pertama))->assertSessionHasNoErrors();

        $this->assertSame(15000, $p->fresh()->total_biaya_pembersihan);
        $this->assertDatabaseHas('aktivitas_logs', ['aksi' => 'hapus_pembersihan', 'peminjaman_id' => $p->id]);
    }

    public function test_catatan_peminjam_tidak_bisa_dihapus_admin(): void
    {
        $p = $this->periksa();
        $log = PerawatanLog::forceCreate([
            'peminjaman_id' => $p->id, 'user_id' => $p->user_id, 'pelaksana' => 'peminjam',
            'tanggal' => now()->toDateString(), 'kegiatan' => 'menyiram', 'biaya' => 0,
        ]);

        $this->actingAs($this->buatUser('admin'))
            ->delete(route('admin.pembersihan.destroy', $log))
            ->assertSessionHasErrors('hapus');

        $this->assertDatabaseHas('perawatan_logs', ['id' => $log->id]);
    }

    // ---------- penyelesaian deposit ----------
    public function test_penyelesaian_deposit_sesuai_aturan(): void
    {
        $admin = $this->buatUser('admin');

        // [biaya, status_deposit, kekurangan, sisa] dengan deposit Rp 100.000
        $kasus = [
            [0, 'dikembalikan', 0, 100000],
            [40000, 'dipotong', 0, 60000],
            [100000, 'terpakai_habis', 0, 0],
            [130000, 'terpakai_habis', 30000, 0],
        ];

        foreach ($kasus as [$biaya, $statusDeposit, $kekurangan, $sisa]) {
            $p = $this->periksa();
            if ($biaya > 0) {
                $this->logAdmin($p, $biaya);
            }

            $this->actingAs($admin)->post(route('admin.peminjamans.selesaikan', $p))->assertSessionHasNoErrors();

            $p->refresh();
            $this->assertSame('dikembalikan', $p->status, "biaya $biaya");
            $this->assertSame($statusDeposit, $p->status_deposit, "biaya $biaya");
            $this->assertSame($kekurangan, $p->kekurangan_bayar, "biaya $biaya");
            $this->assertSame($sisa, $p->saldo_deposit, "biaya $biaya");
            $this->assertNotNull($p->tanggal_deposit_selesai, "biaya $biaya");
        }

        $this->assertDatabaseHas('aktivitas_logs', ['aksi' => 'selesaikan_deposit']);
    }

    public function test_selesaikan_hanya_dari_menunggu_pemeriksaan(): void
    {
        $p = $this->aktif();

        $this->actingAs($this->buatUser('admin'))
            ->post(route('admin.peminjamans.selesaikan', $p))
            ->assertSessionHasErrors('status');

        $this->assertSame('aktif', $p->fresh()->status);
    }

    public function test_setelah_selesai_catatan_tidak_bisa_diubah(): void
    {
        $p = $this->periksa();
        $this->logAdmin($p, 20000);
        $admin = $this->buatUser('admin');

        $this->actingAs($admin)->post(route('admin.peminjamans.selesaikan', $p))->assertSessionHasNoErrors();

        $this->actingAs($admin)
            ->post(route('admin.peminjamans.pembersihan.store', $p), $this->dataPembersihan())
            ->assertSessionHasErrors('status');

        $log = PerawatanLog::firstOrFail();
        $this->actingAs($admin)->delete(route('admin.pembersihan.destroy', $log))->assertSessionHasErrors('hapus');
        $this->assertSame(20000, $p->fresh()->total_biaya_pembersihan);
    }

    public function test_jadwal_lahan_terbuka_setelah_deposit_diselesaikan(): void
    {
        $lahan = $this->buatLahan();
        $p = $this->periksa($lahan);
        $payload = [
            'lahan_id' => $lahan->id,
            'tanggal_mulai' => $this->hari(5),
            'tanggal_selesai' => $this->hari(10),
            'jenis_tanaman' => 'Selada',
            'tujuan' => 'praktikum',
        ];
        $baru = $this->buatUser();

        $this->actingAs($baru)->post(route('peminjam.peminjamans.store'), $payload)->assertSessionHasErrors('tanggal_mulai');

        $this->actingAs($this->buatUser('admin'))->post(route('admin.peminjamans.selesaikan', $p))->assertSessionHasNoErrors();

        $this->actingAs($baru)->post(route('peminjam.peminjamans.store'), $payload)->assertSessionHasNoErrors();
    }

    public function test_peminjam_melihat_rincian_hasil_penyelesaian(): void
    {
        $p = $this->periksa();
        $this->logAdmin($p, 40000);
        $this->actingAs($this->buatUser('admin'))->post(route('admin.peminjamans.selesaikan', $p));

        $this->actingAs($p->user)
            ->get(route('peminjam.peminjamans.show', $p))
            ->assertOk()
            ->assertSee('40.000')
            ->assertSee('60.000');
    }

    public function test_peminjam_tidak_bisa_memakai_route_admin(): void
    {
        $p = $this->periksa();
        $user = $p->user;

        $this->actingAs($user)->post(route('admin.peminjamans.selesaikan', $p))->assertForbidden();
        $this->actingAs($user)->post(route('admin.peminjamans.pembersihan.store', $p), $this->dataPembersihan())->assertForbidden();
    }
}
