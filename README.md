# Sistem Peminjaman Lahan Green House

Aplikasi Laravel untuk peminjaman lahan green house. Deposit yang dibayar peminjam adalah jaminan untuk biaya pembersihan lahan. Jika peminjam tidak membersihkan lahan saat dikembalikan, admin yang membersihkannya dan biayanya dipotong dari deposit.

## Aturan utama

- **Deposit** per lahan disalin ke setiap pengajuan saat diajukan. Perubahan deposit lahan tidak mengubah pengajuan lama.
- **Pembayaran** harus dilaporkan dalam 48 jam setelah pengajuan disetujui. Jika belum dilaporkan sampai batas itu, pengajuan dibatalkan otomatis dan jadwal lahan terbuka lagi. Pengajuan yang sudah dilaporkan tidak dibatalkan otomatis.
- **Laporan pembayaran yang ditolak admin** mendapat tambahan 24 jam.
- **Pengembalian**: peminjam membersihkan lahan lalu mengunggah foto kondisi. Jika tidak dikembalikan sampai 3 hari setelah tanggal selesai, sistem menandainya dikembalikan dan admin membersihkannya.
- **Pembersihan oleh admin** dicatat dengan biaya. Saat deposit diselesaikan, biaya dipotong dari deposit dan sisanya dikembalikan. Jika biaya melebihi deposit, selisihnya dicatat sebagai kekurangan.
- **Catatan perawatan tanaman** oleh peminjam bersifat opsional dan tidak memengaruhi deposit.
- **Jadwal lahan**: tanggal bersifat inklusif. Pengajuan yang masih menunggu belum mengunci jadwal. Pengecekan bentrok dilakukan lagi saat admin menyetujui.

Angka-angka di atas ada di `config/greenhouse.php`:

| Kunci | Nilai | Arti |
|---|---|---|
| `batas_bayar_jam` | 48 | Batas lapor pembayaran setelah disetujui |
| `tolak_bayar_perpanjang_jam` | 24 | Tambahan waktu setelah laporan ditolak |
| `batas_kembali_hari` | 3 | Hari setelah tanggal selesai sebelum lahan ditandai dikembalikan |

## Alur

- Peminjam: ajukan → disetujui → bayar deposit → aktif → kembalikan lahan → menunggu pemeriksaan → (deposit diselesaikan)
- Admin: setujui atau tolak → konfirmasi pembayaran → periksa lahan → catat pembersihan (jika kotor) → selesaikan deposit

## Instalasi

Jalankan dari folder tempat proyek akan dibuat, lalu dari dalam folder proyek:

```bash
composer create-project laravel/laravel greenhouse
cd greenhouse
composer require laravel/breeze --dev
php artisan breeze:install blade
```

Salin isi paket ke proyek (pilih replace), lalu atur `.env`:

```
DB_CONNECTION=mysql
DB_DATABASE=greenhouse
DB_USERNAME=root
DB_PASSWORD=
APP_TIMEZONE=Asia/Jakarta
APP_INSTANSI="Nama Instansi Anda"
```

Lalu:

```bash
php artisan storage:link
php artisan migrate:fresh --seed
npm install
npm run build
```

## Akun demo

| Peran | Email | Password |
|---|---|---|
| Admin | admin@example.com | password |
| Peminjam | peminjam@example.com | password |

Ganti password sebelum dipakai di luar demo. Seeder juga membuat 2 green house dan 8 lahan.

## Menjalankan

Buka dua terminal dari dalam folder proyek:

```bash
php artisan serve
php artisan schedule:work
```

- `php artisan serve` menjalankan aplikasi.
- `php artisan schedule:work` menjalankan pembatalan dan pengembalian otomatis setiap jam. Jika terminal ini ditutup, proses otomatis berhenti.
- Untuk menjalankan proses itu sekali secara manual: `php artisan greenhouse:proses-jadwal`.

Jika tampilan terlihat polos, jalankan `npm run dev` di terminal ketiga, atau `npm run build` sekali.

## Test

```bash
php artisan test
```

Test mencakup peran dan akses, pengajuan dan bentrok jadwal, pembayaran dan bukti privat, pengembalian dan penyelesaian deposit, scheduler, dan dashboard.

## Struktur penting

- `app/Services/PeminjamanService.php`: pengajuan, persetujuan, dan penolakan dengan kunci baris lahan.
- `app/Services/PembayaranService.php`: lapor, konfirmasi, dan tolak pembayaran.
- `app/Services/PengembalianService.php`: pengembalian, pembersihan, dan penyelesaian deposit.
- `app/Services/JadwalService.php`: pembatalan dan pengembalian otomatis.
- `app/Console/Commands/ProsesJadwal.php`: command `greenhouse:proses-jadwal`.
- `routes/console.php`: jadwal per jam untuk command di atas.
- `app/Models/Peminjaman.php`: scope bentrok dan rumus deposit `hitungPenyelesaianDeposit()`.

## Penyimpanan file

- Bukti bayar disimpan di disk `local` (privat). Hanya pemilik dan admin yang bisa membukanya lewat `/bukti-bayar/{id}`.
- Foto perawatan dan foto kondisi lahan disimpan di disk `public`. Nama file diacak, tetapi siapa pun yang tahu URL-nya bisa membukanya.

## Pengecekan tampilan HP

Buka halaman di Chrome, tekan F12, lalu klik ikon perangkat dan pilih ukuran HP (misalnya iPhone 12). Periksa bahwa menu tidak terpotong, tabel bisa digeser ke samping, tombol tidak keluar layar, dan form bisa diisi.

## Yang belum ada

- Pengembalian uang sisa deposit belum dicatat. Sistem hanya mencatat nominalnya.
- Tidak ada notifikasi lewat WhatsApp atau email. Peminjam harus membuka aplikasi untuk melihat tindakan.
- Tidak ada jeda antar peminjaman setelah deposit diselesaikan.
- Tidak ada tarif acuan pembersihan. Admin mengisi biaya secara bebas.
- Pengajuan yang masih `menunggu` setelah tanggal mulainya lewat tidak ditutup otomatis.
- Penguncian baris belum diuji dengan permintaan bersamaan sungguhan.
- Menghapus akun dari halaman profil bisa gagal jika akun itu punya riwayat peminjaman.
