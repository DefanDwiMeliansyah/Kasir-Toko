<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kategori;
use App\Models\Pelanggan;
use App\Models\Penjualan;
use App\Models\Produk;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    function index()
    {
        // KEMBALI KE ORIGINAL - memastikan tidak ada yang rusak
        $user = User::selectRaw('count(*) as jumlah')->first();
        $pelanggan = Pelanggan::selectRaw('count(*) as jumlah')->first();
        $kategori = Kategori::selectRaw('count(*) as jumlah')->first();
        $produk = Produk::selectRaw('count(*) as jumlah')->first();

        // Pastikan semua object memiliki property jumlah
        if (!isset($user->jumlah)) $user = (object) ['jumlah' => 0];
        if (!isset($pelanggan->jumlah)) $pelanggan = (object) ['jumlah' => 0];
        if (!isset($kategori->jumlah)) $kategori = (object) ['jumlah' => 0];
        if (!isset($produk->jumlah)) $produk = (object) ['jumlah' => 0];

        // === Data Chart Penjualan ===
        $penjualan = Penjualan::select(
            DB::raw('SUM(total) as jumlah_total'),
            DB::raw('DATE_FORMAT(tanggal, "%d/%m/%Y") tgl')
        )
            ->whereMonth('tanggal', date('m'))
            ->whereYear('tanggal', date('Y'))
            ->where('status', '!=', 'batal')
            ->groupBy('tgl')
            ->get();

        $nama_bulan = [
            'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];

        $label = 'Transaksi ' . $nama_bulan[date('m') - 1] . ' ' . date('Y');
        $labels = [];
        $data = [];

        foreach ($penjualan as $row) {
            $labels[] = substr($row->tgl, 0, 2);
            $data[] = $row->jumlah_total;
        }

        // === Data Chart Laba Rugi ===
        $labaRugi = DB::table('detil_penjualans')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->join('penjualans', 'detil_penjualans.penjualan_id', '=', 'penjualans.id')
            ->whereMonth('penjualans.tanggal', date('m'))
            ->whereYear('penjualans.tanggal', date('Y'))
            ->where('penjualans.status', '!=', 'batal')
            ->selectRaw('
        DATE_FORMAT(penjualans.tanggal, "%d/%m/%Y") as tgl,
        SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) as pendapatan,
        SUM(detil_penjualans.jumlah * produks.harga_beli) as hpp
    ')
            ->groupBy('tgl')
            ->get();

        $labelsLabaRugi = [];
        $dataLabaRugi = [];

        foreach ($labaRugi as $row) {
            $labelsLabaRugi[] = substr($row->tgl, 0, 2);
            $dataLabaRugi[] = $row->pendapatan - $row->hpp;
        }

        $chartLabaRugi = [
            'label' => 'Laba Rugi ' . $nama_bulan[date('m') - 1] . ' ' . date('Y'),
            'labels' => json_encode($labelsLabaRugi),
            'data' => json_encode($dataLabaRugi)
        ];

        // TAMBAHAN FITUR BARU: Data Chart Produk Terlaris (Top 5) - AMAN
        $labelsProdukTerlaris = [];
        $dataProdukTerlaris = [];
        $colorsProdukTerlaris = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];

        try {
            $produkTerlaris = DB::table('penjualans')
                ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
                ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
                ->whereMonth('penjualans.tanggal', date('m'))
                ->whereYear('penjualans.tanggal', date('Y'))
                ->where('penjualans.status', '!=', 'batal')
                ->groupBy('produks.id', 'produks.nama_produk')
                ->selectRaw('
                    produks.nama_produk,
                    SUM(detil_penjualans.jumlah) as total_terjual
                ')
                ->orderBy('total_terjual', 'DESC')
                ->limit(5)
                ->get();

            foreach ($produkTerlaris as $produkItem) {
                $labelsProdukTerlaris[] = $produkItem->nama_produk;
                $dataProdukTerlaris[] = $produkItem->total_terjual;
            }
        } catch (\Exception $e) {
            // Jika error query produk terlaris, biarkan array kosong
            $labelsProdukTerlaris = [];
            $dataProdukTerlaris = [];
        }

        $chartProdukTerlaris = [
            'label' => 'Top 5 Produk Terlaris ' . $nama_bulan[date('m') - 1] . ' ' . date('Y'),
            'labels' => json_encode($labelsProdukTerlaris),
            'data' => json_encode($dataProdukTerlaris),
            'colors' => json_encode(array_slice($colorsProdukTerlaris, 0, count($dataProdukTerlaris)))
        ];

        return view('welcome', [
            'user' => $user,
            'pelanggan' => $pelanggan,
            'kategori' => $kategori,
            'produk' => $produk,
            'cart' => [
                'label' => $label,
                'labels' => json_encode($labels),
                'data' => json_encode($data)
            ],
            'chartLabaRugi' => $chartLabaRugi,
            'chartProdukTerlaris' => $chartProdukTerlaris
        ]);
    }
}