<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use Illuminate\Support\Facades\DB;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.form');
    }

    public function harian(Request $request)
    {
        $penjualan = Penjualan::join('users', 'users.id', 'penjualans.user_id')
            ->join('pelanggans', 'pelanggans.id', 'penjualans.pelanggan_id')
            ->whereDate('tanggal', $request->tanggal)
            ->select('penjualans.*', 'pelanggans.nama as nama_pelanggan', 'users.nama as nama_kasir')
            ->orderBy('id')
            ->get();

        return view('laporan.harian', [
            'penjualan' => $penjualan
        ]);
    }

public function bulanan(Request $request)
{
    $data = Penjualan::select(
        DB::raw('DATE(tanggal) as tanggal'),
        DB::raw('SUM(CASE WHEN status != "batal" THEN total ELSE 0 END) as jumlah_total'),
        DB::raw('COUNT(CASE WHEN status != "batal" THEN 1 ELSE NULL END) as jumlah_selesai'),
        DB::raw('COUNT(CASE WHEN status = "batal" THEN 1 ELSE NULL END) as jumlah_batal'),
        DB::raw('COUNT(id) as jumlah_transaksi')
    )
        ->whereMonth('tanggal', $request->bulan)
        ->whereYear('tanggal', $request->tahun)
        ->groupBy(DB::raw('DATE(tanggal)'))
        ->orderBy('tanggal')
        ->get();

    $nama_bulan = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    $bulan = $nama_bulan[$request->bulan - 1] ?? 'Bulan Tidak Valid';

    return view('laporan.bulanan', [
        'penjualan' => $data,
        'bulan' => $bulan,
    ]);
}
/**
 * Laporan Laba Rugi Harian
 */
public function labaRugiHarian(Request $request)
{
    $tanggal = $request->tanggal ?: date('Y-m-d');
    
    $labaRugi = DB::table('penjualans')
        ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
        ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
        ->whereDate('penjualans.tanggal', $tanggal) // pakai whereDate
        ->where('penjualans.status', '!=', 'batal') // jangan pakai status = selesai saja
        ->selectRaw('
            SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) as pendapatan,
            SUM(detil_penjualans.jumlah * produks.harga_beli) as hpp,
            SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli) as laba_rugi
        ')
        ->first();

    if (!$labaRugi->pendapatan) {
        $labaRugi->pendapatan = 0;
        $labaRugi->hpp = 0;
        $labaRugi->laba_rugi = 0;
    }

    return view('laporan.laba-rugi-harian', [
        'labaRugi' => $labaRugi,
        'tanggal' => $tanggal
    ]);
}

/**
 * Laporan Laba Rugi Bulanan
 */
public function labaRugiBulanan(Request $request)
{
    $bulan = $request->bulan;
    $tahun = $request->tahun;
    
    if (!$bulan || !$tahun) {
        return back()->with('error', 'Bulan dan tahun harus dipilih');
    }

    // Get data per hari dalam bulan tersebut
    $labaRugi = DB::table('penjualans')
        ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
        ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
        ->whereMonth('penjualans.tanggal', $bulan)
        ->whereYear('penjualans.tanggal', $tahun)
        ->where('penjualans.status', 'selesai')
        ->groupBy('penjualans.tanggal')
        ->selectRaw('
            penjualans.tanggal,
            SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) as pendapatan,
            SUM(detil_penjualans.jumlah * produks.harga_beli) as hpp,
            SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli) as laba_rugi
        ')
        ->orderBy('penjualans.tanggal')
        ->get();

    $bulanName = [
        '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];

    return view('laporan.laba-rugi-bulanan', [
        'labaRugi' => $labaRugi,
        'bulan' => $bulanName[$bulan],
        'tahun' => $tahun
    ]);
}

/**
 * Data untuk chart laba rugi dashboard (12 bulan terakhir)
 */
public function getLabaRugiChartData()
{
    $data = [];
    $labels = [];
    
    for ($i = 11; $i >= 0; $i--) {
        $date = now()->subMonths($i);
        $month = $date->month;
        $year = $date->year;
        
        $labaRugi = DB::table('penjualans')
            ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->whereMonth('penjualans.tanggal', $month)
            ->whereYear('penjualans.tanggal', $year)
            ->where('penjualans.status', 'selesai')
            ->selectRaw('
                SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli) as laba_rugi
            ')
            ->first();

        $labels[] = $date->format('M Y');
        $data[] = $labaRugi->laba_rugi ?? 0;
    }

    return [
        'labels' => json_encode($labels),
        'data' => json_encode($data),
        'label' => 'Laba Rugi Bulanan'
    ];
}
}