<?php

namespace Tests\Feature;

use App\Models\Peminjaman;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\MembuatData;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, MembuatData;

    public function test_admin_mengubah_status_dan_deposit_dari_dashboard(): void
    {
        $lahan = $this->buatLahan(['deposit' => 100000]);

        $this->actingAs($this->buatUser('admin'))
            ->patch(route('admin.lahans.cepat', $lahan), ['status' => 'perawatan', 'deposit' => 150000])
            ->assertSessionHasNoErrors();

        $lahan->refresh();
        $this->assertSame('perawatan', $lahan->status);
        $this->assertSame(150000, $lahan->deposit);
        $this->assertDatabaseHas('aktivitas_logs', ['aksi' => 'ubah_cepat_lahan', 'lahan_id' => $lahan->id]);
    }

    public function test_tanpa_perubahan_tidak_membuat_log(): void
    {
        $lahan = $this->buatLahan(['deposit' => 100000]);

        $this->actingAs($this->buatUser('admin'))
            ->patch(route('admin.lahans.cepat', $lahan), ['status' => 'tersedia', 'deposit' => 100000])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('aktivitas_logs', ['aksi' => 'ubah_cepat_lahan']);
    }

    public function test_status_dan_deposit_divalidasi(): void
    {
        $lahan = $this->buatLahan();
        $admin = $this->buatUser('admin');
        $url = route('admin.lahans.cepat', $lahan);

        $this->actingAs($admin)->patch($url, ['status' => 'rusak', 'deposit' => 100000])->assertSessionHasErrors('status');
        $this->actingAs($admin)->patch($url, ['status' => 'tersedia', 'deposit' => -5])->assertSessionHasErrors('deposit');
    }

    public function test_peminjam_tidak_bisa_memakai_ubah_cepat(): void
    {
        $lahan = $this->buatLahan();

        $this->actingAs($this->buatUser())
            ->patch(route('admin.lahans.cepat', $lahan), ['status' => 'perawatan', 'deposit' => 1])
            ->assertForbidden();
    }

    public function test_perubahan_deposit_tidak_mengubah_peminjaman_yang_sudah_ada(): void
    {
        $lahan = $this->buatLahan(['deposit' => 100000]);
        $p = $this->buatPeminjaman($this->buatUser(), $lahan, 'aktif', $this->hari(-5), $this->hari(10));

        $this->actingAs($this->buatUser('admin'))
            ->patch(route('admin.lahans.cepat', $lahan), ['status' => 'tersedia', 'deposit' => 300000]);

        $this->assertSame(100000, $p->fresh()->nominal_deposit);
    }

    public function test_status_perawatan_tidak_menghentikan_peminjaman_aktif(): void
    {
        $lahan = $this->buatLahan();
        $p = $this->buatPeminjaman($this->buatUser(), $lahan, 'aktif', $this->hari(-5), $this->hari(10));

        $this->actingAs($this->buatUser('admin'))
            ->patch(route('admin.lahans.cepat', $lahan), ['status' => 'perawatan', 'deposit' => 100000]);

        $this->assertSame('aktif', $p->fresh()->status);
    }

    public function test_dashboard_admin_menampilkan_pengajuan_dan_lahan(): void
    {
        $lahan = $this->buatLahan(['kode' => 'ADMIN-01']);
        $p = $this->buatPeminjaman($this->buatUser(), $lahan, 'menunggu', $this->hari(5), $this->hari(10));

        $this->actingAs($this->buatUser('admin'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Pengajuan baru')
            ->assertSee($p->user->name)
            ->assertSee('ADMIN-01');
    }

    public function test_dashboard_admin_menghitung_laporan_bayar(): void
    {
        $p = $this->buatPeminjaman($this->buatUser(), $this->buatLahan(), 'disetujui', $this->hari(5), $this->hari(10));
        $p->forceFill(['tanggal_bayar' => now(), 'metode_bayar' => 'transfer', 'batas_bayar' => now()->addDay()])->save();

        $this->actingAs($this->buatUser('admin'))
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('Dilaporkan');
    }

    public function test_dashboard_peminjam_menampilkan_tindakan_bayar_dan_kembali(): void
    {
        $peminjam = $this->buatUser();

        $bayar = $this->buatPeminjaman($peminjam, $this->buatLahan(['kode' => 'BAYAR-01']), 'disetujui', $this->hari(5), $this->hari(10));
        $bayar->forceFill(['batas_bayar' => now()->addHours(10)])->save();

        $this->buatPeminjaman($peminjam, $this->buatLahan(['kode' => 'KEMBALI-01']), 'aktif', $this->hari(-10), $this->hari(-1));

        $this->actingAs($peminjam)
            ->get(route('peminjam.dashboard'))
            ->assertOk()
            ->assertSee('Bayar deposit lahan BAYAR-01')
            ->assertSee('Kembalikan lahan KEMBALI-01');
    }

    public function test_dashboard_peminjam_tanpa_tindakan(): void
    {
        $this->actingAs($this->buatUser())
            ->get(route('peminjam.dashboard'))
            ->assertOk()
            ->assertSee('Tidak ada tindakan yang perlu dilakukan');
    }
}
