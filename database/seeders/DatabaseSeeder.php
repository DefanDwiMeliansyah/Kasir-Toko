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
        // =======================
        // USERS
        // =======================
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

        \App\Models\User::create([
            'nama'     => 'Aldhi Ekanda',
            'username' => 'aldhi',
            'password' => bcrypt('password'),
            'role'     => 'admin',
        ]);

        \App\Models\User::create([
            'nama'     => 'Patah Yasin',
            'username' => 'patah',
            'password' => bcrypt('password'),
            'role'     => 'petugas',
        ]);

        \App\Models\User::create([
            'nama'     => 'Rizky Ramadhan',
            'username' => 'rizky',
            'password' => bcrypt('password'),
            'role'     => 'petugas',
        ]);

        // =======================
        // PELANGGAN
        // =======================
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

        \App\Models\Pelanggan::create([
            'nama' => 'Sinta Lestari',
            'alamat' => 'Ciamis',
            'nomor_tlp' => '082299911122'
        ]);

        \App\Models\Pelanggan::create([
            'nama' => 'Bagus Firmansyah',
            'alamat' => 'Banjar',
            'nomor_tlp' => '082233344455'
        ]);

        \App\Models\Pelanggan::create([
            'nama' => 'Melati Ayu',
            'alamat' => 'Pangandaran',
            'nomor_tlp' => '082277766655'
        ]);

        // =======================
        // KATEGORI
        // =======================
        \App\Models\Kategori::create(['nama_kategori' => 'Makanan']);
        \App\Models\Kategori::create(['nama_kategori' => 'Minuman']);
        \App\Models\Kategori::create(['nama_kategori' => 'Kecantikan']);
        \App\Models\Kategori::create(['nama_kategori' => 'Kesehatan']);
        \App\Models\Kategori::create(['nama_kategori' => 'Elektronik']);

        // =======================
        // PRODUK MAKANAN
        // =======================
        \App\Models\Produk::create(['kategori_id' => 1, 'kode_produk' => '1001', 'nama_produk' => 'Chiki Taro', 'harga' => 5000, 'harga_beli' => 3500, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 1, 'kode_produk' => '1002', 'nama_produk' => 'Indomie Goreng', 'harga' => 3500, 'harga_beli' => 2500, 'stok' => 200]);
        \App\Models\Produk::create(['kategori_id' => 1, 'kode_produk' => '1003', 'nama_produk' => 'Silverqueen Coklat', 'harga' => 12000, 'harga_beli' => 9000, 'stok' => 150]);
        \App\Models\Produk::create(['kategori_id' => 1, 'kode_produk' => '1004', 'nama_produk' => 'Roma Malkist', 'harga' => 8000, 'harga_beli' => 6000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 1, 'kode_produk' => '1005', 'nama_produk' => 'Kacang Garuda', 'harga' => 10000, 'harga_beli' => 7500, 'stok' => 100]);

        // =======================
        // PRODUK MINUMAN
        // =======================
        \App\Models\Produk::create(['kategori_id' => 2, 'kode_produk' => '2001', 'nama_produk' => 'Lee Mineral', 'harga' => 3500, 'harga_beli' => 2500, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 2, 'kode_produk' => '2002', 'nama_produk' => 'Teh Botol Sosro', 'harga' => 6000, 'harga_beli' => 4500, 'stok' => 120]);
        \App\Models\Produk::create(['kategori_id' => 2, 'kode_produk' => '2003', 'nama_produk' => 'Coca Cola', 'harga' => 7000, 'harga_beli' => 5000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 2, 'kode_produk' => '2004', 'nama_produk' => 'Fruit Tea', 'harga' => 6500, 'harga_beli' => 4800, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 2, 'kode_produk' => '2005', 'nama_produk' => 'Good Day Cappuccino', 'harga' => 5000, 'harga_beli' => 3500, 'stok' => 150]);

        // =======================
        // PRODUK KECANTIKAN
        // =======================
        \App\Models\Produk::create(['kategori_id' => 3, 'kode_produk' => '3001', 'nama_produk' => 'Lipstik Wardah', 'harga' => 45000, 'harga_beli' => 33000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 3, 'kode_produk' => '3002', 'nama_produk' => 'Bedak Marcks', 'harga' => 25000, 'harga_beli' => 18000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 3, 'kode_produk' => '3003', 'nama_produk' => 'Serum Scarlett', 'harga' => 75000, 'harga_beli' => 55000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 3, 'kode_produk' => '3004', 'nama_produk' => 'Masker Naturgo', 'harga' => 15000, 'harga_beli' => 11000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 3, 'kode_produk' => '3005', 'nama_produk' => 'Shampoo Sunsilk', 'harga' => 30000, 'harga_beli' => 22000, 'stok' => 100]);

        // =======================
        // PRODUK KESEHATAN
        // =======================
        \App\Models\Produk::create(['kategori_id' => 4, 'kode_produk' => '4001', 'nama_produk' => 'Paracetamol', 'harga' => 10000, 'harga_beli' => 7500, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 4, 'kode_produk' => '4002', 'nama_produk' => 'Vitamin C 1000mg', 'harga' => 20000, 'harga_beli' => 15000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 4, 'kode_produk' => '4003', 'nama_produk' => 'Antangin JRG', 'harga' => 12000, 'harga_beli' => 9000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 4, 'kode_produk' => '4004', 'nama_produk' => 'Minyak Kayu Putih', 'harga' => 15000, 'harga_beli' => 11000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 4, 'kode_produk' => '4005', 'nama_produk' => 'Hansaplast', 'harga' => 8000, 'harga_beli' => 6000, 'stok' => 100]);

        // =======================
        // PRODUK ELEKTRONIK
        // =======================
        \App\Models\Produk::create(['kategori_id' => 5, 'kode_produk' => '5001', 'nama_produk' => 'Kipas Angin Miyako', 'harga' => 150000, 'harga_beli' => 110000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 5, 'kode_produk' => '5002', 'nama_produk' => 'Lampu Philips 12 Watt', 'harga' => 40000, 'harga_beli' => 30000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 5, 'kode_produk' => '5003', 'nama_produk' => 'Setrika Maspion', 'harga' => 175000, 'harga_beli' => 130000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 5, 'kode_produk' => '5004', 'nama_produk' => 'Magic Com Cosmos', 'harga' => 300000, 'harga_beli' => 225000, 'stok' => 100]);
        \App\Models\Produk::create(['kategori_id' => 5, 'kode_produk' => '5005', 'nama_produk' => 'Speaker Bluetooth JBL', 'harga' => 500000, 'harga_beli' => 375000, 'stok' => 100]);


        // =======================
        // STOK AWAL (Produk Lama)
        // =======================
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

        \App\Models\Produk::where('id', 1)->update(['stok' => 250]);
        \App\Models\Produk::where('id', 2)->update(['stok' => 100]);

        // =======================
        // PENJUALAN (Data Lama)
        // =======================
        \App\Models\Penjualan::create([
            'user_id' => 1,
            'pelanggan_id' => 1,
            'nomor_transaksi' => date('Ymd') . '0001',
            'tanggal' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'subtotal' => 85000,
            'pajak' => 8500,
            'total' => 93500,
            'tunai' => 100000,
            'kembalian' => 6500
        ]);

        \App\Models\Penjualan::create([
            'user_id' => 2,
            'pelanggan_id' => 2,
            'nomor_transaksi' => date('Ymd') . '0002',
            'tanggal' => date('Y-m-d H:i:s', strtotime('-1 day')),
            'subtotal' => 135000,
            'pajak' => 13500,
            'total' => 148500,
            'tunai' => 200000,
            'kembalian' => 51500
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

        // =======================
        // DISKON (Data Lama)
        // =======================
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
            'kondisi_ids' => [1],
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
            'kuota' => null,
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
            'kondisi_ids' => [1],
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
