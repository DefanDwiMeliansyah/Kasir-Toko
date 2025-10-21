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
use App\Http\Controllers\ExpiredController;

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

    // Dashboard - ROUTE UTAMA UNTUK HOME
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
        // Routes laporan yang sudah ada
    Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
    Route::post('/laporan/harian', [LaporanController::class, 'harian'])->name('laporan.harian');
    Route::post('/laporan/bulanan', [LaporanController::class, 'bulanan'])->name('laporan.bulanan');
    
    // Routes laporan laba rugi detail baru
    Route::post('/laporan/laba-rugi-harian', [LaporanController::class, 'labaRugiHarian'])->name('laporan.laba-rugi-harian');
    Route::post('/laporan/laba-rugi-bulanan', [LaporanController::class, 'labaRugiBulanan'])->name('laporan.laba-rugi-bulanan');
    
    // Route untuk chart data (jika diperlukan untuk dashboard)
    Route::get('/laporan/chart-data', [LaporanController::class, 'getLabaRugiChartData'])->name('laporan.chart-data');
    
    // FITUR BARU: Routes laporan produk terlaris
    Route::post('/laporan/produk-terlaris', [LaporanController::class, 'produkTerlaris'])->name('laporan.produk-terlaris');
    Route::get('/laporan/produk-terlaris-chart', [LaporanController::class, 'getProdukTerlarisChartData'])->name('laporan.produk-terlaris-chart');

    // Diskon
    Route::resource('diskon', DiskonController::class);
    Route::patch('diskon/{diskon}/toggle', [DiskonController::class, 'toggle'])->name('diskon.toggle');

    Route::resource('expired', \App\Http\Controllers\ExpiredController::class)->except(['edit', 'update', 'show']);
    Route::get('expired/produk', [\App\Http\Controllers\ExpiredController::class, 'produk'])->name('expired.produk');
});