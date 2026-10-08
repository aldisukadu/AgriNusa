<?php

use App\Http\Controllers\Admin\GreenHouseController;
use App\Http\Controllers\Admin\LahanController as AdminLahanController;
use App\Http\Controllers\Admin\PembayaranController as AdminPembayaranController;
use App\Http\Controllers\Admin\PengembalianController as AdminPengembalianController;
use App\Http\Controllers\Admin\PeminjamanController as AdminPeminjamanController;
use App\Http\Controllers\BuktiBayarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Peminjam\LahanController as PeminjamLahanController;
use App\Http\Controllers\Peminjam\PembayaranController as PeminjamPembayaranController;
use App\Http\Controllers\Peminjam\PeminjamanController as PeminjamPeminjamanController;
use App\Http\Controllers\Peminjam\PengembalianController as PeminjamPengembalianController;
use App\Http\Controllers\Peminjam\PerawatanController as PeminjamPerawatanController;
use App\Http\Controllers\ProfileController;
use App\Http\Middleware\RoleMiddleware;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->isAdmin() ? 'admin.dashboard' : 'peminjam.dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/bukti-bayar/{peminjaman}', [BuktiBayarController::class, 'show'])->name('bukti-bayar');
});

Route::middleware(['auth', RoleMiddleware::class.':admin'])
    ->prefix('admin')->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'admin'])->name('dashboard');

        Route::resource('green-houses', GreenHouseController::class)
            ->except('show')->parameters(['green-houses' => 'green_house']);
        Route::resource('lahans', AdminLahanController::class)
            ->except('show')->parameters(['lahans' => 'lahan']);

        Route::get('peminjamans', [AdminPeminjamanController::class, 'index'])->name('peminjamans.index');
        Route::get('peminjamans/{peminjaman}', [AdminPeminjamanController::class, 'show'])->name('peminjamans.show');
        Route::post('peminjamans/{peminjaman}/setujui', [AdminPeminjamanController::class, 'setujui'])->name('peminjamans.setujui');
        Route::post('peminjamans/{peminjaman}/tolak', [AdminPeminjamanController::class, 'tolak'])->name('peminjamans.tolak');

        Route::post('peminjamans/{peminjaman}/bayar/konfirmasi', [AdminPembayaranController::class, 'konfirmasi'])->name('peminjamans.bayar.konfirmasi');
        Route::post('peminjamans/{peminjaman}/bayar/tolak', [AdminPembayaranController::class, 'tolak'])->name('peminjamans.bayar.tolak');

        Route::post('peminjamans/{peminjaman}/kembalikan', [AdminPengembalianController::class, 'kembalikan'])->name('peminjamans.kembalikan');
        Route::post('peminjamans/{peminjaman}/pembersihan', [AdminPengembalianController::class, 'catat'])->name('peminjamans.pembersihan.store');
        Route::post('peminjamans/{peminjaman}/selesaikan', [AdminPengembalianController::class, 'selesaikan'])->name('peminjamans.selesaikan');
        Route::delete('pembersihan/{perawatan_log}', [AdminPengembalianController::class, 'hapus'])->name('pembersihan.destroy');
    });

Route::middleware(['auth', RoleMiddleware::class.':peminjam'])
    ->prefix('peminjam')->name('peminjam.')
    ->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'peminjam'])->name('dashboard');
        Route::get('lahans', [PeminjamLahanController::class, 'index'])->name('lahans.index');
        Route::resource('peminjamans', PeminjamPeminjamanController::class)
            ->only(['index', 'create', 'store', 'show'])->parameters(['peminjamans' => 'peminjaman']);

        Route::post('peminjamans/{peminjaman}/bayar', [PeminjamPembayaranController::class, 'store'])->name('peminjamans.bayar');
        Route::post('peminjamans/{peminjaman}/perawatan', [PeminjamPerawatanController::class, 'store'])->name('peminjamans.perawatan.store');
        Route::post('peminjamans/{peminjaman}/kembali', [PeminjamPengembalianController::class, 'store'])->name('peminjamans.kembali');
    });

require __DIR__.'/auth.php';
