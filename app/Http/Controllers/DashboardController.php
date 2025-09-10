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
        $user = User::selectRaw('count(*) as jumlah')->first();
        $pelanggan = Pelanggan::selectRaw('count(*) as jumlah')->first();
        $kategori = Kategori::selectRaw('count(*) as jumlah')->first();
        $produk = Produk::selectRaw('count(*) as jumlah')->first();

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
            'chartLabaRugi' => $chartLabaRugi
        ]);
    }
}
