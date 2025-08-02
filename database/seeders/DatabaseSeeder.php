<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'nama'     => 'Administrator',
            'username' => 'admin',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        \App\Models\User::create([
            'nama'     => 'Petugas',
            'username' => 'petugas',
            'password' => bcrypt('password'),
            'role'     => 'petugas',
        ]);

        \App\Models\Pelanggan::create([
            'nama' => 'Dodo Sidodo',
            'alamat' => 'Padaherang',
            'nomor_tlp' => '082288877766'
        ]);

        \App\Models\Pelanggan::create([
            'nama' => 'Hanifah',
            'alamat' => 'Kalipucang',
            'nomor_tlp' => '082288866677'
        ]);

        \App\Models\Kategori::create([
            'nama_kategori' => 'Makanan',
        ]);

        \App\Models\Kategori::create([
            'nama_kategori' => 'Minuman',
        ]);

        \App\Models\Produk::create([
            'kategori_id' => 1,
            'kode_produk' => '1001',
            'nama_produk' => 'Chiki Taro',
            'harga' => 5000
        ]);

        \App\Models\Produk::create([
            'kategori_id' => 2,
            'kode_produk' => '1002',
            'nama_produk' => 'Le Mineral',
            'harga' => 3500
        ]);

        \App\Models\Stok::create([
            'produk_id' => 1,
            'nama_suplier' => 'Toko Haji Usman',
            'jumlah' => 250,
            'tanggal' => date('Y-m-d', strtotime('-1 week'))
        ]);

        \App\Models\Stok::create([
            'produk_id' => 2,
            'nama_suplier' => 'Agen Le Mineral',
            'jumlah' => 100,
            'tanggal' => date('Y-m-d', strtotime('-1 week'))
        ]);

        \App\Models\Produk::where('id', 1)->update([
            'stok' => 250,
        ]);

        \App\Models\Produk::where('id', 2)->update([
            'stok' => 100,
        ]);

        \App\Models\Penjualan::create([
            'user_id' => 1,
            'pelanggan_id' => 1,
            'nomor_transaksi' => date('Ymd') . '0001',
            'tanggal' => date('Y-m-d H:i:s'),
            'subtotal' => 8500,
            'pajak' => 850,
            'total' => 9350,
            'tunai' => 10000,
            'kembalian' => 650
        ]);

        \App\Models\Penjualan::create([
            'user_id' => 2,
            'pelanggan_id' => 2,
            'nomor_transaksi' => date('Ymd') . '0002',
            'tanggal' => date('Y-m-d H:i:s'),
            'subtotal' => 13500,
            'pajak' => 1350,
            'total' => 14850,
            'tunai' => 20000,
            'kembalian' => 5150
        ]);

        \App\Models\DetilPenjualan::create([
            'penjualan_id' => 1,
            'produk_id' => 1,
            'jumlah' => 1,
            'harga_produk' => 5000,
            'subtotal' => 5000,
        ]);

        \App\Models\DetilPenjualan::create([
            'penjualan_id' => 1,
            'produk_id' => 2,
            'jumlah' => 1,
            'harga_produk' => 3500,
            'subtotal' => 3500,
        ]);

        \App\Models\DetilPenjualan::create([
            'penjualan_id' => 2,
            'produk_id' => 1,
            'jumlah' => 1,
            'harga_produk' => 5000,
            'subtotal' => 10000,
        ]);

        \App\Models\DetilPenjualan::create([
            'penjualan_id' => 2,
            'produk_id' => 2,
            'jumlah' => 1,
            'harga_produk' => 3500,
            'subtotal' => 3500,
        ]);

        $now = Carbon::now();

        \App\Models\Diskon::create([
            'kode_diskon' => 'DISKON10',
            'nama_diskon' => 'Diskon 10% Semua Produk',
            'deskripsi' => 'Dapatkan diskon 10% untuk semua produk dengan minimal belanja Rp 50.000',
            'jenis_diskon' => 'persen',
            'nilai_diskon' => 10,
            'maksimal_diskon' => 25000,
            'minimal_belanja' => 50000,
            'jenis_kondisi' => 'semua',
            'kondisi_ids' => null,
            'kuota' => 100,
            'terpakai' => 0,
            'tanggal_mulai' => $now,
            'tanggal_berakhir' => $now->copy()->addMonth(),
            'aktif' => true
        ]);

        \App\Models\Diskon::create([
            'kode_diskon' => 'MAKANAN15',
            'nama_diskon' => 'Diskon 15% Kategori Makanan',
            'deskripsi' => 'Diskon khusus untuk kategori makanan dengan minimal belanja Rp 30.000',
            'jenis_diskon' => 'persen',
            'nilai_diskon' => 15,
            'maksimal_diskon' => 15000,
            'minimal_belanja' => 30000,
            'jenis_kondisi' => 'kategori',
            'kondisi_ids' => [1], // ID kategori makanan
            'kuota' => 50,
            'terpakai' => 0,
            'tanggal_mulai' => $now,
            'tanggal_berakhir' => $now->copy()->addWeeks(2),
            'aktif' => true
        ]);

        \App\Models\Diskon::create([
            'kode_diskon' => 'HEMAT5000', 
            'nama_diskon' => 'Hemat Rp 5.000',
            'deskripsi' => 'Potongan langsung Rp 5.000 untuk pembelian minimal Rp 25.000',
            'jenis_diskon' => 'nominal',
            'nilai_diskon' => 5000,
            'maksimal_diskon' => null,
            'minimal_belanja' => 25000,
            'jenis_kondisi' => 'semua',
            'kondisi_ids' => null,
            'kuota' => null, // unlimited
            'terpakai' => 0,
            'tanggal_mulai' => $now,
            'tanggal_berakhir' => $now->copy()->addDays(7),
            'aktif' => true
        ]);

        \App\Models\Diskon::create([
            'kode_diskon' => 'CHIKI20',
            'nama_diskon' => 'Diskon 20% Chiki Taro', 
            'deskripsi' => 'Diskon khusus untuk produk Chiki Taro',
            'jenis_diskon' => 'persen',
            'nilai_diskon' => 20,
            'maksimal_diskon' => 10000,
            'minimal_belanja' => 0,
            'jenis_kondisi' => 'produk',
            'kondisi_ids' => [1], // ID produk Chiki Taro
            'kuota' => 25,
            'terpakai' => 0,
            'tanggal_mulai' => $now,
            'tanggal_berakhir' => $now->copy()->addDays(3),
            'aktif' => true
        ]);

        \App\Models\Diskon::create([
            'kode_diskon' => 'EXPIRED',
            'nama_diskon' => 'Diskon Expired (Contoh)',
            'deskripsi' => 'Contoh diskon yang sudah expired',
            'jenis_diskon' => 'persen',
            'nilai_diskon' => 25,
            'maksimal_diskon' => null,
            'minimal_belanja' => 0,
            'jenis_kondisi' => 'semua',
            'kondisi_ids' => null,
            'kuota' => 10,
            'terpakai' => 5,
            'tanggal_mulai' => $now->copy()->subWeeks(2),
            'tanggal_berakhir' => $now->copy()->subWeek(),
            'aktif' => true
        ]);
    }
}
