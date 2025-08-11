@extends('layouts.laporan', ['title' => 'Laporan Bulanan'])
@section('content')
<h1 class="text-center">Laporan Bulanan</h1>

<p>Bulan: {{ $bulan }} {{ request()->tahun }}</p>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Transaksi Selesai</th>
            <th>Transaksi Batal</th>
            <th>Jumlah Transaksi</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($penjualan as $key => $row)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ date('d/m/Y', strtotime($row->tanggal)) }}</td>
            <td>{{ $row->jumlah_selesai }}</td>
            <td>{{ $row->jumlah_batal }}</td>
            <td>{{ $row->jumlah_transaksi }}</td>
            <td>{{ number_format($row->jumlah_total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr>
            <th colspan="5">Jumlah Total</th>
            <th>{{ number_format($penjualan->sum('jumlah_total'), 0, ',', '.') }}</th>
        </tr>
    </tfoot>
</table>
@endsection
