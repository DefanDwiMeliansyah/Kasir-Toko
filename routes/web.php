<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PelangganController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\ProdukController;
use App\Http\Controllers\StokController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DiskonController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Semua route web aplikasi dikelompokkan berdasarkan kategori.
|
*/

// ======================
// ROUTE UMUM
// ======================
Route::get('/', function () {
    return view('welcome');
})->name('home')->middleware('auth');

Route::view('login', 'auth.login')->name('login')->middleware('guest');

// ======================
// ROUTE AUTHENTIKASI
// ======================
Route::post('login', [AuthController::class, 'login'])->middleware('guest');
Route::post('logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ======================
// ROUTE DENGAN MIDDLEWARE AUTH
// ======================
Route::middleware('auth')->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('home');

    // Profile
    Route::singleton('profile', ProfileController::class);

    // User (Hanya Admin)
    Route::resource('user', UserController::class)->middleware('can:admin');

    // Pelanggan
    Route::resource('pelanggan', PelangganController::class);

    // Kategori (Hanya Admin)
    Route::resource('kategori', KategoriController::class)->middleware('can:admin');

    // Produk
    Route::resource('produk', ProdukController::class);

    // Stok
    Route::get('stok/produk', [StokController::class, 'produk'])->name('stok.produk');
    Route::resource('stok', StokController::class)->only(['index', 'create', 'store', 'destroy']);

    // Transaksi
    Route::get('transaksi/produk', [TransaksiController::class, 'produk'])->name('transaksi.produk');
    Route::get('transaksi/pelanggan', [TransaksiController::class, 'pelanggan'])->name('transaksi.pelanggan');
    Route::post('transaksi/pelanggan', [TransaksiController::class, 'addPelanggan'])->name('transaksi.pelanggan.add');
    Route::get('transaksi/cetak', [TransaksiController::class, 'cetak'])->name('transaksi.cetak');
    Route::resource('transaksi', TransaksiController::class)->except('edit', 'update');

    // Cart
    Route::get('cart/clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('cart/apply-discount', [CartController::class, 'applyDiscount']);
    Route::post('cart/remove-discount', [CartController::class, 'removeDiscount']);
    Route::resource('cart', CartController::class)
        ->except('create', 'show', 'edit')
        ->parameters(['cart' => 'hash']);

    // Laporan
    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::get('laporan/harian', [LaporanController::class, 'harian'])->name('laporan.harian');
    Route::get('laporan/bulanan', [LaporanController::class, 'bulanan'])->name('laporan.bulanan');

    // Diskon
    Route::resource('diskon', DiskonController::class);
    Route::patch('diskon/{diskon}/toggle', [DiskonController::class, 'toggle'])->name('diskon.toggle');
});
