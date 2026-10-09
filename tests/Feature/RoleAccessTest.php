<?php

namespace Tests\Feature;

use App\Models\GreenHouse;
use App\Models\Lahan;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAccessTest extends TestCase
{
    use RefreshDatabase;

    private function buatUser(string $role): User
    {
        $user = User::factory()->create();
        $user->role = $role;
        $user->save();

        return $user;
    }

    private function buatPeminjaman(User $pemilik): Peminjaman
    {
        $gh = GreenHouse::create(['nama' => 'GH Test', 'lokasi' => 'Test']);
        $lahan = Lahan::create([
            'green_house_id' => $gh->id, 'kode' => 'T-01', 'luas' => 4,
            'media_tanam' => 'Tanah', 'deposit' => 100000,
        ]);

        return Peminjaman::create([
            'user_id' => $pemilik->id, 'lahan_id' => $lahan->id,
            'tanggal_mulai' => '2026-11-01', 'tanggal_selesai' => '2026-11-30',
            'jenis_tanaman' => 'Selada', 'tujuan' => 'praktikum', 'nominal_deposit' => 100000,
        ]);
    }

    public function test_registrasi_selalu_menjadi_peminjam_walau_mengirim_role_admin(): void
    {
        $this->post('/register', [
            'name' => 'Pendaftar',
            'email' => 'pendaftar@example.com',
            'status_pengguna' => 'mahasiswa',
            'no_hp' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'admin',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'pendaftar@example.com',
            'role' => 'peminjam',
            'no_hp' => '081234567890',
        ]);
    }

    public function test_tamu_diarahkan_ke_login(): void
    {
        $this->get('/admin/dashboard')->assertRedirect('/login');
    }

    public function test_peminjam_tidak_boleh_membuka_halaman_admin(): void
    {
        $this->actingAs($this->buatUser('peminjam'))->get('/admin/dashboard')->assertForbidden();
    }

    public function test_admin_tidak_boleh_membuka_halaman_peminjam(): void
    {
        $this->actingAs($this->buatUser('admin'))->get('/peminjam/dashboard')->assertForbidden();
    }

    public function test_dashboard_mengarahkan_sesuai_role(): void
    {
        $this->actingAs($this->buatUser('admin'))->get('/dashboard')->assertRedirect('/admin/dashboard');
        $this->actingAs($this->buatUser('pekerja'))->get('/dashboard')->assertRedirect('/pekerja/dashboard');
        $this->actingAs($this->buatUser('peminjam'))->get('/dashboard')->assertRedirect('/peminjam/dashboard');
    }

    public function test_pekerja_bisa_melihat_peminjaman_tetapi_tidak_mengelola_data_admin(): void
    {
        $pekerja = $this->buatUser('pekerja');
        $peminjaman = $this->buatPeminjaman($this->buatUser('peminjam'));

        $this->actingAs($pekerja)->get(route('pekerja.dashboard'))->assertOk();
        $this->actingAs($pekerja)->get(route('pekerja.peminjamans.index'))->assertOk();
        $this->actingAs($pekerja)->get(route('pekerja.peminjamans.show', $peminjaman))
            ->assertOk()
            ->assertDontSee('Setujui')
            ->assertDontSee('Konfirmasi pembayaran');
        $this->actingAs($pekerja)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($pekerja)->get(route('admin.lahans.index'))->assertForbidden();
        $this->actingAs($pekerja)->get(route('admin.pekerja.index'))->assertForbidden();
        $this->actingAs($pekerja)->post(route('admin.peminjamans.setujui', $peminjaman))->assertForbidden();
    }

    public function test_admin_dapat_membuat_akun_pekerja(): void
    {
        $admin = $this->buatUser('admin');

        $this->actingAs($admin)->post(route('admin.pekerja.store'), [
            'name' => 'Petugas Green House',
            'email' => 'petugas@example.com',
            'no_hp' => '081234567890',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect(route('admin.pekerja.index'));

        $pekerja = User::where('email', 'petugas@example.com')->firstOrFail();
        $this->assertSame('pekerja', $pekerja->role);
        $this->assertTrue(password_verify('password123', $pekerja->password));
    }

    public function test_policy_peminjaman(): void
    {
        $pemilik = $this->buatUser('peminjam');
        $lain = $this->buatUser('peminjam');
        $admin = $this->buatUser('admin');
        $data = $this->buatPeminjaman($pemilik);

        $this->assertTrue($pemilik->can('view', $data));
        $this->assertFalse($lain->can('view', $data));
        $this->assertFalse($lain->can('update', $data));
        $this->assertTrue($admin->can('view', $data));
    }
}
