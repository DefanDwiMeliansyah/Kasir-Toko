@extends('layouts.laporan', ['title' => 'Laporan Laba Rugi Harian Detail'])
@section('content')
<h1 class="text-center">Laporan Laba Rugi Harian - Detail</h1>

<p><strong>Tanggal:</strong> {{ date('d/m/Y', strtotime($tanggal)) }}</p>

<!-- Tabel Detail Laba Rugi per Produk -->
<div class="mt-4">
    <h3>Detail Laba Rugi per Produk</h3>
    <div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="table-dark">
                <tr>
                    <th>No</th>
                    <th>Kode Produk</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th>
                    <th>Qty Terjual</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Total HPP</th>
                    <th>Total Pendapatan</th>
                    <th>Laba</th>
                    <th>Margin (%)</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($detailProduk as $key => $item)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $item->kode_produk }}</td>
                    <td>{{ $item->nama_produk }}</td>
                    <td>{{ $item->nama_kategori }}</td>
                    <td class="text-center">{{ number_format($item->jumlah_terjual, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->harga_beli, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->harga_jual, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->total_hpp, 0, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($item->total_pendapatan, 0, ',', '.') }}</td>
                    <td class="text-right {{ $item->laba >= 0 ? 'text-success' : 'text-danger' }}">
                        <strong>{{ number_format($item->laba, 0, ',', '.') }}</strong>
                    </td>
                    <td class="text-center {{ $item->margin_persen >= 0 ? 'text-success' : 'text-danger' }}">
                        <strong>{{ number_format($item->margin_persen, 2, ',', '.') }}%</strong>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" class="text-center">Tidak ada data transaksi pada tanggal ini</td>
                </tr>
                @endforelse
            </tbody>
            @if ($detailProduk->count() > 0)
            <tfoot class="table-secondary">
                <tr>
                    <th colspan="7">Total Keseluruhan</th>
                    <th class="text-right">{{ number_format($detailProduk->sum('total_hpp'), 0, ',', '.') }}</th>
                    <th class="text-right">{{ number_format($detailProduk->sum('total_pendapatan'), 0, ',', '.') }}</th>
                    <th class="text-right {{ $detailProduk->sum('laba') >= 0 ? 'text-success' : 'text-danger' }}">
                        <strong>{{ number_format($detailProduk->sum('laba'), 0, ',', '.') }}</strong>
                    </th>
                    <th class="text-center {{ $ringkasan->margin_keseluruhan >= 0 ? 'text-success' : 'text-danger' }}">
                        <strong>{{ number_format($ringkasan->margin_keseluruhan, 2, ',', '.') }}%</strong>
                    </th>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

<!-- Tabel Ringkasan Laba Rugi -->
<div class="mt-4">
    <h3>Ringkasan Laba Rugi</h3>
    <div class="row">
        <div class="col-md-8">
            <table class="table table-bordered table-sm">
                <thead class="table-info">
                    <tr>
                        <th>Keterangan</th>
                        <th class="text-right">Jumlah (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>Total Pendapatan</strong></td>
                        <td class="text-right">{{ number_format($ringkasan->total_pendapatan, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total HPP (Harga Pokok Penjualan)</strong></td>
                        <td class="text-right">{{ number_format($ringkasan->total_hpp, 0, ',', '.') }}</td>
                    </tr>
                    <tr class="{{ $ringkasan->total_laba >= 0 ? 'table-success' : 'table-danger' }}">
                        <td><strong>{{ $ringkasan->total_laba >= 0 ? 'TOTAL LABA' : 'TOTAL RUGI' }}</strong></td>
                        <td class="text-right">
                            <strong>{{ number_format($ringkasan->total_laba, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                    <tr class="{{ $ringkasan->margin_keseluruhan >= 0 ? 'table-success' : 'table-danger' }}">
                        <td><strong>MARGIN KESELURUHAN</strong></td>
                        <td class="text-right">
                            <strong>{{ number_format($ringkasan->margin_keseluruhan, 2, ',', '.') }}%</strong>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-4">
    <small class="text-muted">
        <strong>Catatan:</strong><br>
        * HPP = Harga Beli × Jumlah Terjual<br>
        * Pendapatan = Harga Jual × Jumlah Terjual<br>
        * Laba = Pendapatan - HPP<br>
        * Margin (%) = (Laba ÷ Pendapatan) × 100%<br>
        * Data hanya menampilkan transaksi yang tidak dibatalkan
    </small>
</div>
@endsection