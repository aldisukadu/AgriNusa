<?php

namespace Tests\Feature;

use App\Models\PerawatanLog;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class PembayaranPerawatanTest extends TestCase
{
    use RefreshDatabase, MembuatData;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
    }

    private function disetujui(array $ubah = []): Peminjaman
    {
        $p = $this->buatPeminjaman($this->buatUser(), $this->buatLahan(), 'disetujui', $this->hari(1), $this->hari(30));
        $p->forceFill(array_merge(['batas_bayar' => now()->addHours(48)], $ubah))->save();

        return $p;
    }

    private function aktif(array $ubah = []): Peminjaman
    {
        $p = $this->buatPeminjaman($this->buatUser(), $this->buatLahan(), 'aktif', $this->hari(-1), $this->hari(30));
        $p->forceFill(array_merge([
            'status_deposit' => 'dibayar',
            'metode_bayar' => 'tunai',
            'tanggal_bayar' => now()->subDay(),
            'aktif_sejak' => now()->subDay(),
        ], $ubah))->save();

        return $p;
    }

    private function dataPerawatan(array $ubah = []): array
    {
        return array_merge([
            'tanggal' => now()->toDateString(),
            'kegiatan' => 'menyiram',
            'catatan' => 'Siram pagi',
        ], $ubah);
    }

    public function test_transfer_wajib_bukti_dan_file_tersimpan_privat(): void
    {
        $p = $this->disetujui();
        $url = route('peminjam.peminjamans.bayar', $p);

        $this->actingAs($p->user)->post($url, ['metode_bayar' => 'transfer'])->assertSessionHasErrors('bukti_bayar');

        $this->actingAs($p->user)->post($url, [
            'metode_bayar' => 'transfer',
            'bukti_bayar' => UploadedFile::fake()->create('bukti.jpg', 100, 'image/jpeg'),
        ])->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertNotNull($p->tanggal_bayar);
        $this->assertSame('transfer', $p->metode_bayar);
        Storage::disk('local')->assertExists($p->bukti_bayar);
    }

    public function test_tunai_tanpa_bukti_diterima(): void
    {
        $p = $this->disetujui();

        $this->actingAs($p->user)
            ->post(route('peminjam.peminjamans.bayar', $p), ['metode_bayar' => 'tunai'])
            ->assertSessionHasNoErrors();

        $this->assertNotNull($p->fresh()->tanggal_bayar);
    }

    public function test_lapor_setelah_batas_bayar_ditolak(): void
    {
        $p = $this->disetujui(['batas_bayar' => now()->subHour()]);

        $this->actingAs($p->user)
            ->post(route('peminjam.peminjamans.bayar', $p), ['metode_bayar' => 'tunai'])
            ->assertSessionHasErrors('pembayaran');

        $this->assertNull($p->fresh()->tanggal_bayar);
    }

    public function test_peminjam_lain_tidak_bisa_melapor_pembayaran(): void
    {
        $p = $this->disetujui();

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.bayar', $p), ['metode_bayar' => 'tunai'])
            ->assertForbidden();
    }

    public function test_admin_konfirmasi_membuat_peminjaman_aktif(): void
    {
        $p = $this->disetujui(['metode_bayar' => 'tunai', 'tanggal_bayar' => now()]);

        $this->actingAs($this->buatUser('admin'))
            ->post(route('admin.peminjamans.bayar.konfirmasi', $p))
            ->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertSame('aktif', $p->status);
        $this->assertSame('dibayar', $p->status_deposit);
        $this->assertNotNull($p->aktif_sejak);
        $this->assertDatabaseHas('aktivitas_logs', ['aksi' => 'konfirmasi_pembayaran', 'peminjaman_id' => $p->id]);
    }

    public function test_konfirmasi_langsung_tanpa_laporan_butuh_metode(): void
    {
        $p = $this->disetujui();
        $admin = $this->buatUser('admin');
        $url = route('admin.peminjamans.bayar.konfirmasi', $p);

        $this->actingAs($admin)->post($url)->assertSessionHasErrors('metode_bayar');
        $this->assertSame('disetujui', $p->fresh()->status);

        $this->actingAs($admin)->post($url, ['metode_bayar' => 'tunai'])->assertSessionHasNoErrors();
        $this->assertSame('aktif', $p->fresh()->status);
    }

    public function test_tolak_laporan_menghapus_bukti_dan_memperpanjang_batas(): void
    {
        Storage::disk('local')->put('bukti-bayar/palsu.jpg', 'x');
        $p = $this->disetujui([
            'metode_bayar' => 'transfer',
            'bukti_bayar' => 'bukti-bayar/palsu.jpg',
            'tanggal_bayar' => now(),
            'batas_bayar' => now()->addHour(),
        ]);

        $this->actingAs($this->buatUser('admin'))
            ->post(route('admin.peminjamans.bayar.tolak', $p), ['alasan' => 'Nominal tidak sesuai'])
            ->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertNull($p->tanggal_bayar);
        $this->assertNull($p->bukti_bayar);
        $this->assertTrue($p->batas_bayar->gt(now()->addHours(20)));
        Storage::disk('local')->assertMissing('bukti-bayar/palsu.jpg');
    }

    public function test_bukti_bayar_hanya_untuk_pemilik_dan_admin(): void
    {
        Storage::disk('local')->put('bukti-bayar/ada.jpg', 'x');
        $p = $this->disetujui(['bukti_bayar' => 'bukti-bayar/ada.jpg', 'tanggal_bayar' => now(), 'metode_bayar' => 'transfer']);
        $url = route('bukti-bayar', $p);

        $this->actingAs($p->user)->get($url)->assertOk();
        $this->actingAs($this->buatUser('admin'))->get($url)->assertOk();
        $this->actingAs($this->buatUser())->get($url)->assertForbidden();
    }

    public function test_bukti_bayar_tidak_ada_menghasilkan_404(): void
    {
        $p = $this->disetujui();

        $this->actingAs($p->user)->get(route('bukti-bayar', $p))->assertNotFound();
    }

    public function test_peminjam_mencatat_perawatan_tanpa_foto(): void
    {
        $p = $this->aktif();

        $this->actingAs($p->user)
            ->post(route('peminjam.peminjamans.perawatan.store', $p), $this->dataPerawatan())
            ->assertSessionHasNoErrors();

        $log = PerawatanLog::firstOrFail();
        $this->assertSame('peminjam', $log->pelaksana);
        $this->assertSame(0, $log->biaya);
        $this->assertNull($log->foto);
        $this->assertSame($p->user->id, $log->user_id);
    }

    public function test_perawatan_dengan_foto_tersimpan(): void
    {
        $p = $this->aktif();

        $this->actingAs($p->user)->post(
            route('peminjam.peminjamans.perawatan.store', $p),
            $this->dataPerawatan(['foto' => UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg')])
        )->assertSessionHasNoErrors();

        Storage::disk('public')->assertExists(PerawatanLog::firstOrFail()->foto);
    }

    public function test_tanggal_perawatan_dibatasi_satu_hari_ke_belakang(): void
    {
        $p = $this->aktif();
        $url = route('peminjam.peminjamans.perawatan.store', $p);

        $this->actingAs($p->user)->post($url, $this->dataPerawatan(['tanggal' => $this->hari(1)]))->assertSessionHasErrors('tanggal');
        $this->actingAs($p->user)->post($url, $this->dataPerawatan(['tanggal' => $this->hari(-3)]))->assertSessionHasErrors('tanggal');
        $this->actingAs($p->user)->post($url, $this->dataPerawatan(['tanggal' => $this->hari(-1)]))->assertSessionHasNoErrors();

        $this->assertSame(1, PerawatanLog::count());
    }

    public function test_perawatan_tidak_bisa_memakai_kegiatan_pembersihan(): void
    {
        $p = $this->aktif();

        $this->actingAs($p->user)
            ->post(route('peminjam.peminjamans.perawatan.store', $p), $this->dataPerawatan(['kegiatan' => 'pembersihan']))
            ->assertSessionHasErrors('kegiatan');
    }

    public function test_perawatan_hanya_saat_aktif(): void
    {
        $p = $this->disetujui();

        $this->actingAs($p->user)
            ->post(route('peminjam.peminjamans.perawatan.store', $p), $this->dataPerawatan())
            ->assertSessionHasErrors('status');
    }

    public function test_peminjam_lain_tidak_bisa_mencatat_perawatan(): void
    {
        $p = $this->aktif();

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.perawatan.store', $p), $this->dataPerawatan())
            ->assertForbidden();
    }
}
