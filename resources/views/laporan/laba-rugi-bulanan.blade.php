@extends('layouts.laporan', ['title' => 'Laporan Laba Rugi Bulanan'])
@section('content')
<h1 class="text-center">Laporan Laba Rugi Bulanan</h1>

<p>Bulan: {{ $bulan }} {{ $tahun }}</p>

<table class="table table-bordered table-sm">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Pendapatan</th>
            <th>HPP</th>
            <th>Laba</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($labaRugi as $key => $row)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ date('d/m/Y', strtotime($row->tanggal)) }}</td>
            <td class="text-right">{{ number_format($row->pendapatan, 0, ',', '.') }}</td>
            <td class="text-right">{{ number_format($row->hpp, 0, ',', '.') }}</td>
            <td class="text-right {{ $row->laba_rugi >= 0 ? 'text-success' : 'text-danger' }}">
                <strong>{{ number_format($row->laba_rugi, 0, ',', '.') }}</strong>
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="5" class="text-center">Tidak ada data transaksi pada periode ini</td>
        </tr>
        @endforelse
    </tbody>
    @if ($labaRugi->count() > 0)
    <tfoot>
        <tr class="table-secondary">
            <th colspan="2">Jumlah Total</th>
            <th class="text-right">{{ number_format($labaRugi->sum('pendapatan'), 0, ',', '.') }}</th>
            <th class="text-right">{{ number_format($labaRugi->sum('hpp'), 0, ',', '.') }}</th>
            <th class="text-right {{ $labaRugi->sum('laba_rugi') >= 0 ? 'text-success' : 'text-danger' }}">
                <strong>{{ number_format($labaRugi->sum('laba_rugi'), 0, ',', '.') }}</strong>
            </th>
        </tr>
    </tfoot>
    @endif
</table>

<div class="mt-4">
    <small class="text-muted">
        * HPP dihitung berdasarkan harga beli produk<br>
        * Laba = Pendapatan - HPP<br>
        * Data hanya menampilkan tanggal yang memiliki transaksi
    </small>
</div>
@endsection