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
     * Laporan Laba Rugi Harian dengan Detail
     */
    public function labaRugiHarian(Request $request)
    {
        $tanggal = $request->tanggal ?: date('Y-m-d');
        
        // Detail laba rugi per produk
        $detailProduk = DB::table('penjualans')
            ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->join('kategoris', 'produks.kategori_id', '=', 'kategoris.id')
            ->whereDate('penjualans.tanggal', $tanggal)
            ->where('penjualans.status', '!=', 'batal')
            ->groupBy('produks.id', 'produks.kode_produk', 'produks.nama_produk', 
                     'kategoris.nama_kategori', 'produks.harga_beli', 'detil_penjualans.harga_produk')
            ->selectRaw('
                produks.kode_produk,
                produks.nama_produk,
                kategoris.nama_kategori,
                produks.harga_beli,
                detil_penjualans.harga_produk as harga_jual,
                SUM(detil_penjualans.jumlah) as jumlah_terjual,
                SUM(detil_penjualans.jumlah * produks.harga_beli) as total_hpp,
                SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) as total_pendapatan,
                (SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli)) as laba,
                CASE 
                    WHEN SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) > 0 
                    THEN ROUND(((SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli)) / SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk)) * 100, 2)
                    ELSE 0 
                END as margin_persen
            ')
            ->orderBy('produks.nama_produk')
            ->get();

        // Ringkasan total
        $ringkasan = DB::table('penjualans')
            ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->whereDate('penjualans.tanggal', $tanggal)
            ->where('penjualans.status', '!=', 'batal')
            ->selectRaw('
                SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) as total_pendapatan,
                SUM(detil_penjualans.jumlah * produks.harga_beli) as total_hpp,
                (SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli)) as total_laba,
                CASE 
                    WHEN SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) > 0 
                    THEN ROUND(((SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli)) / SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk)) * 100, 2)
                    ELSE 0 
                END as margin_keseluruhan
            ')
            ->first();

        if (!$ringkasan->total_pendapatan) {
            $ringkasan->total_pendapatan = 0;
            $ringkasan->total_hpp = 0;
            $ringkasan->total_laba = 0;
            $ringkasan->margin_keseluruhan = 0;
        }

        return view('laporan.laba-rugi-harian-detail', [
            'detailProduk' => $detailProduk,
            'ringkasan' => $ringkasan,
            'tanggal' => $tanggal
        ]);
    }

    /**
     * Laporan Laba Rugi Bulanan dengan Detail
     */
    public function labaRugiBulanan(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        
        if (!$bulan || !$tahun) {
            return back()->with('error', 'Bulan dan tahun harus dipilih');
        }

        // Detail laba rugi per produk
        $detailProduk = DB::table('penjualans')
            ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->join('kategoris', 'produks.kategori_id', '=', 'kategoris.id')
            ->whereMonth('penjualans.tanggal', $bulan)
            ->whereYear('penjualans.tanggal', $tahun)
            ->where('penjualans.status', '!=', 'batal')
            ->groupBy('produks.id', 'produks.kode_produk', 'produks.nama_produk', 
                     'kategoris.nama_kategori', 'produks.harga_beli', 'detil_penjualans.harga_produk')
            ->selectRaw('
                produks.kode_produk,
                produks.nama_produk,
                kategoris.nama_kategori,
                produks.harga_beli,
                detil_penjualans.harga_produk as harga_jual,
                SUM(detil_penjualans.jumlah) as jumlah_terjual,
                SUM(detil_penjualans.jumlah * produks.harga_beli) as total_hpp,
                SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) as total_pendapatan,
                (SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli)) as laba,
                CASE 
                    WHEN SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) > 0 
                    THEN ROUND(((SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli)) / SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk)) * 100, 2)
                    ELSE 0 
                END as margin_persen
            ')
            ->orderBy('produks.nama_produk')
            ->get();

        // Detail laba rugi per hari
        $detailHarian = DB::table('penjualans')
            ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->whereMonth('penjualans.tanggal', $bulan)
            ->whereYear('penjualans.tanggal', $tahun)
            ->where('penjualans.status', '!=', 'batal')
            ->groupBy(DB::raw('DATE(penjualans.tanggal)'))
            ->selectRaw('
                DATE(penjualans.tanggal) as tanggal,
                COUNT(DISTINCT penjualans.id) as jumlah_transaksi,
                SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) as pendapatan,
                SUM(detil_penjualans.jumlah * produks.harga_beli) as hpp,
                (SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli)) as laba,
                CASE 
                    WHEN SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) > 0 
                    THEN ROUND(((SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli)) / SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk)) * 100, 2)
                    ELSE 0 
                END as margin_persen
            ')
            ->orderBy('tanggal')
            ->get();

        // Ringkasan total
        $ringkasan = DB::table('penjualans')
            ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->whereMonth('penjualans.tanggal', $bulan)
            ->whereYear('penjualans.tanggal', $tahun)
            ->where('penjualans.status', '!=', 'batal')
            ->selectRaw('
                COUNT(DISTINCT penjualans.id) as total_transaksi,
                SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) as total_pendapatan,
                SUM(detil_penjualans.jumlah * produks.harga_beli) as total_hpp,
                (SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli)) as total_laba,
                CASE 
                    WHEN SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) > 0 
                    THEN ROUND(((SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) - SUM(detil_penjualans.jumlah * produks.harga_beli)) / SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk)) * 100, 2)
                    ELSE 0 
                END as margin_keseluruhan
            ')
            ->first();

        if (!$ringkasan->total_pendapatan) {
            $ringkasan->total_transaksi = 0;
            $ringkasan->total_pendapatan = 0;
            $ringkasan->total_hpp = 0;
            $ringkasan->total_laba = 0;
            $ringkasan->margin_keseluruhan = 0;
        }

        $bulanName = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        return view('laporan.laba-rugi-bulanan-detail', [
            'detailProduk' => $detailProduk,
            'detailHarian' => $detailHarian,
            'ringkasan' => $ringkasan,
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

    /**
     * FITUR BARU: Laporan Produk Terlaris Bulanan
     */
    public function produkTerlaris(Request $request)
    {
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        
        if (!$bulan || !$tahun) {
            return back()->with('error', 'Bulan dan tahun harus dipilih');
        }

        // Data produk terlaris
        $produkTerlaris = DB::table('penjualans')
            ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->join('kategoris', 'produks.kategori_id', '=', 'kategoris.id')
            ->whereMonth('penjualans.tanggal', $bulan)
            ->whereYear('penjualans.tanggal', $tahun)
            ->where('penjualans.status', '!=', 'batal')
            ->groupBy('produks.id', 'produks.kode_produk', 'produks.nama_produk', 
                     'kategoris.nama_kategori', 'produks.harga')
            ->selectRaw('
                produks.kode_produk,
                produks.nama_produk,
                kategoris.nama_kategori,
                produks.harga,
                SUM(detil_penjualans.jumlah) as total_terjual,
                COUNT(DISTINCT penjualans.id) as frekuensi_pembelian,
                SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) as total_pendapatan
            ')
            ->orderBy('total_terjual', 'DESC')
            ->get();

        // Ringkasan keseluruhan
        $ringkasan = DB::table('penjualans')
            ->join('detil_penjualans', 'penjualans.id', '=', 'detil_penjualans.penjualan_id')
            ->whereMonth('penjualans.tanggal', $bulan)
            ->whereYear('penjualans.tanggal', $tahun)
            ->where('penjualans.status', '!=', 'batal')
            ->selectRaw('
                COUNT(DISTINCT produks.id) as total_produk_terjual,
                SUM(detil_penjualans.jumlah) as total_kuantitas,
                COUNT(DISTINCT penjualans.id) as total_transaksi,
                SUM(detil_penjualans.jumlah * detil_penjualans.harga_produk) as total_pendapatan
            ')
            ->join('produks', 'detil_penjualans.produk_id', '=', 'produks.id')
            ->first();

        if (!$ringkasan->total_pendapatan) {
            $ringkasan->total_produk_terjual = 0;
            $ringkasan->total_kuantitas = 0;
            $ringkasan->total_transaksi = 0;
            $ringkasan->total_pendapatan = 0;
        }

        $bulanName = [
            '', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        return view('laporan.produk-terlaris', [
            'produkTerlaris' => $produkTerlaris,
            'ringkasan' => $ringkasan,
            'bulan' => $bulanName[$bulan],
            'tahun' => $tahun
        ]);
    }

    /**
     * FITUR BARU: Data Chart Produk Terlaris untuk Dashboard (Top 5)
     */
    public function getProdukTerlarisChartData()
    {
        // Ambil 5 produk terlaris bulan ini
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

        $labels = [];
        $data = [];
        $colors = ['#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF'];

        foreach ($produkTerlaris as $index => $produk) {
            $labels[] = $produk->nama_produk;
            $data[] = $produk->total_terjual;
        }

        return [
            'labels' => json_encode($labels),
            'data' => json_encode($data),
            'colors' => json_encode(array_slice($colors, 0, count($data))),
            'label' => 'Top 5 Produk Terlaris Bulan ' . date('F Y')
        ];
    }
}