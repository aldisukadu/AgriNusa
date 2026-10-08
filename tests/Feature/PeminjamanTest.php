<?php

namespace Tests\Feature;

use App\Models\Lahan;
use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class PeminjamanTest extends TestCase
{
    use RefreshDatabase, MembuatData;

    private function payload(Lahan $lahan, int $mulai, int $selesai): array
    {
        return [
            'lahan_id' => $lahan->id,
            'tanggal_mulai' => $this->hari($mulai),
            'tanggal_selesai' => $this->hari($selesai),
            'jenis_tanaman' => 'Selada',
            'tujuan' => 'praktikum',
        ];
    }

    public function test_pengajuan_menyalin_deposit_dan_status_awal(): void
    {
        $lahan = $this->buatLahan(['deposit' => 150000]);

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.store'), $this->payload($lahan, 5, 30))
            ->assertSessionHasNoErrors();

        $p = Peminjaman::firstOrFail();
        $this->assertSame('menunggu', $p->status);
        $this->assertSame('belum_dibayar', $p->status_deposit);
        $this->assertSame(150000, $p->nominal_deposit);

        $lahan->update(['deposit' => 999999]);
        $this->assertSame(150000, $p->fresh()->nominal_deposit);
    }

    public function test_tanggal_lampau_ditolak(): void
    {
        $lahan = $this->buatLahan();

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.store'), $this->payload($lahan, -3, 5))
            ->assertSessionHasErrors('tanggal_mulai');

        $this->assertSame(0, Peminjaman::count());
    }

    public function test_lahan_perawatan_tidak_bisa_diajukan(): void
    {
        $lahan = $this->buatLahan(['status' => 'perawatan']);

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.store'), $this->payload($lahan, 5, 30))
            ->assertSessionHasErrors('lahan_id');
    }

    public function test_pengajuan_bentrok_dengan_peminjaman_disetujui_ditolak(): void
    {
        $lahan = $this->buatLahan();
        $this->buatPeminjaman($this->buatUser(), $lahan, 'disetujui', $this->hari(10), $this->hari(20));

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.store'), $this->payload($lahan, 20, 25))
            ->assertSessionHasErrors('tanggal_mulai');

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.store'), $this->payload($lahan, 21, 25))
            ->assertSessionHasNoErrors();
    }

    public function test_pengajuan_menunggu_tidak_memblokir_pengajuan_lain(): void
    {
        $lahan = $this->buatLahan();
        $this->buatPeminjaman($this->buatUser(), $lahan, 'menunggu', $this->hari(10), $this->hari(20));

        $this->actingAs($this->buatUser())
            ->post(route('peminjam.peminjamans.store'), $this->payload($lahan, 12, 18))
            ->assertSessionHasNoErrors();

        $this->assertSame(2, Peminjaman::count());
    }

    public function test_admin_menyetujui_dan_batas_bayar_terisi(): void
    {
        $p = $this->buatPeminjaman($this->buatUser(), $this->buatLahan(), 'menunggu', $this->hari(10), $this->hari(20));

        $this->actingAs($this->buatUser('admin'))
            ->post(route('admin.peminjamans.setujui', $p))
            ->assertSessionHasNoErrors();

        $p->refresh();
        $this->assertSame('disetujui', $p->status);
        $this->assertEqualsWithDelta(now()->addHours(48)->timestamp, $p->batas_bayar->timestamp, 10);
        $this->assertDatabaseHas('aktivitas_logs', ['aksi' => 'setujui_peminjaman', 'peminjaman_id' => $p->id]);
    }

    public function test_menyetujui_pengajuan_kedua_yang_bentrok_gagal(): void
    {
        $lahan = $this->buatLahan();
        $a = $this->buatPeminjaman($this->buatUser(), $lahan, 'menunggu', $this->hari(10), $this->hari(20));
        $b = $this->buatPeminjaman($this->buatUser(), $lahan, 'menunggu', $this->hari(15), $this->hari(25));
        $admin = $this->buatUser('admin');

        $this->actingAs($admin)->post(route('admin.peminjamans.setujui', $a))->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.peminjamans.setujui', $b))->assertSessionHasErrors('tanggal_mulai');

        $this->assertSame('disetujui', $a->fresh()->status);
        $this->assertSame('menunggu', $b->fresh()->status);
    }

    public function test_admin_menolak_dengan_catatan(): void
    {
        $p = $this->buatPeminjaman($this->buatUser(), $this->buatLahan(), 'menunggu', $this->hari(10), $this->hari(20));

        $this->actingAs($this->buatUser('admin'))
            ->post(route('admin.peminjamans.tolak', $p), ['catatan_admin' => 'Lahan disiapkan untuk praktikum.'])
            ->assertSessionHasNoErrors();

        $this->assertSame('ditolak', $p->fresh()->status);
        $this->actingAs($this->buatUser('admin'))
            ->post(route('admin.peminjamans.tolak', $p), [])
            ->assertSessionHasErrors('catatan_admin');
    }

    public function test_peminjam_lain_tidak_bisa_melihat_detail(): void
    {
        $p = $this->buatPeminjaman($this->buatUser(), $this->buatLahan(), 'menunggu', $this->hari(10), $this->hari(20));

        $this->actingAs($this->buatUser())->get(route('peminjam.peminjamans.show', $p))->assertForbidden();
    }

    public function test_pemilik_bisa_melihat_detail(): void
    {
        $pemilik = $this->buatUser();
        $p = $this->buatPeminjaman($pemilik, $this->buatLahan(), 'menunggu', $this->hari(10), $this->hari(20));

        $this->actingAs($pemilik)->get(route('peminjam.peminjamans.show', $p))->assertOk();
    }

    public function test_lahan_dengan_riwayat_tidak_bisa_dihapus(): void
    {
        $lahan = $this->buatLahan();
        $this->buatPeminjaman($this->buatUser(), $lahan, 'dibatalkan', $this->hari(10), $this->hari(20));

        $this->actingAs($this->buatUser('admin'))
            ->delete(route('admin.lahans.destroy', $lahan))
            ->assertSessionHasErrors('hapus');

        $this->assertDatabaseHas('lahans', ['id' => $lahan->id]);
    }

    public function test_admin_menambah_lahan_dan_tercatat_di_log(): void
    {
        $lahan = $this->buatLahan();

        $this->actingAs($this->buatUser('admin'))->post(route('admin.lahans.store'), [
            'green_house_id' => $lahan->green_house_id,
            'kode' => 'BARU-01',
            'luas' => 6.5,
            'media_tanam' => 'Cocopeat',
            'deposit' => 200000,
            'status' => 'tersedia',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('lahans', ['kode' => 'BARU-01', 'deposit' => 200000]);
        $this->assertDatabaseHas('aktivitas_logs', ['aksi' => 'tambah_lahan']);
    }
}
