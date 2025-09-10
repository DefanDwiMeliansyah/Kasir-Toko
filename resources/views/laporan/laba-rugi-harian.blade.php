@extends('layouts.laporan', ['title' => 'Laporan Laba Rugi Harian'])
@section('content')
<h1 class="text-center">Laporan Laba Rugi Harian</h1>

<p>Tanggal: {{ date('d/m/Y', strtotime($tanggal)) }}</p>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>Keterangan</th>
            <th>Jumlah (Rp)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td><strong>Pendapatan</strong></td>
            <td class="text-right">{{ number_format($labaRugi->pendapatan, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td><strong>HPP (Harga Pokok Penjualan)</strong></td>
            <td class="text-right">{{ number_format($labaRugi->hpp, 0, ',', '.') }}</td>
        </tr>
        <tr class="{{ $labaRugi->laba_rugi >= 0 ? 'table-success' : 'table-danger' }}">
            <td><strong>{{ $labaRugi->laba_rugi >= 0 ? 'LABA' : 'RUGI' }}</strong></td>
            <td class="text-right"><strong>{{ number_format($labaRugi->laba_rugi, 0, ',', '.') }}</strong></td>
        </tr>
    </tbody>
</table>

<div class="mt-4">
    <small class="text-muted">
        * HPP dihitung berdasarkan harga beli produk<br>
        * Laba = Pendapatan - HPP
    </small>
</div>
@endsection