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
}