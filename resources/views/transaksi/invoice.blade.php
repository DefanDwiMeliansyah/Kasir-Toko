@extends('layouts.main', ['title' => 'Invoice'])
@section('title-content')
<i class="fas fa-file-invoice mr-2"></i>
Invoice #{{ $penjualan->nomor_transaksi }}
@endsection

@section('content')
<div class="card card-orange card-outline">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-file-invoice mr-2"></i> 
            Invoice Transaksi
        </h3>
        <div class="card-tools">
            <a href="{{ route('transaksi.cetak') }}?transaksi={{ $penjualan->id }}" 
               class="btn btn-sm btn-primary" target="_blank">
                <i class="fas fa-print mr-1"></i> Cetak
            </a>
            @if($penjualan->status !== 'batal')
            <form action="{{ route('transaksi.destroy', $penjualan) }}" method="POST" class="d-inline ml-2"
                  onsubmit="return confirm('Yakin ingin membatalkan transaksi ini?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="fas fa-times mr-1"></i> Batalkan
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="card-body">
        <div class="row mb-4">
            <div class="col-md-6">
                <h5>Informasi Transaksi</h5>
                <table class="table table-borderless">
                    <tr>
                        <td width="150">Nomor Transaksi</td>
                        <td>: {{ $penjualan->nomor_transaksi }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal</td>
                        <td>: {{ date('d/m/Y H:i:s', strtotime($penjualan->tanggal)) }}</td>
                    </tr>
                    <tr>
                        <td>Status</td>
                        <td>: 
                            @if($penjualan->status == 'selesai')
                                <span class="badge badge-success">Selesai</span>
                            @else
                                <span class="badge badge-danger">Batal</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                <h5>Informasi Pelanggan & Kasir</h5>
                <table class="table table-borderless">
                    <tr>
                        <td width="100">Pelanggan</td>
                        <td>: {{ $pelanggan->nama }}</td>
                    </tr>
                    <tr>
                        <td>Alamat</td>
                        <td>: {{ $pelanggan->alamat }}</td>
                    </tr>
                    <tr>
                        <td>Kasir</td>
                        <td>: {{ $user->nama }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <h5>Detail Produk</h5>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Nama Produk</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($detilPenjualan as $key => $detail)
                <tr>
                    <td>{{ $key + 1 }}</td>
                    <td>{{ $detail->nama_produk }}</td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>Rp {{ number_format($detail->harga_produk, 0, ',', '.') }}</td>
                    <td>Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="row mt-4">
            <div class="col-md-6 offset-md-6">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Subtotal</strong></td>
                        <td class="text-right"><strong>Rp {{ number_format($penjualan->subtotal, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Pajak (10%)</td>
                        <td class="text-right">Rp {{ number_format($penjualan->pajak, 0, ',', '.') }}</td>
                    </tr>
                    @if($penjualan->diskon_nominal > 0)
                    <tr>
                        <td>
                            Diskon
                            @if($diskon)
                                <br><small class="text-muted">({{ $diskon->kode_diskon }} - {{ $diskon->nama_diskon }})</small>
                            @endif
                        </td>
                        <td class="text-right text-success">
                            <strong>-Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</strong>
                        </td>
                    </tr>
                    @endif
                    <tr class="border-top">
                        <td><strong>Total Bayar</strong></td>
                        <td class="text-right"><strong>Rp {{ number_format($penjualan->total, 0, ',', '.') }}</strong></td>
                    </tr>
                    <tr>
                        <td>Tunai</td>
                        <td class="text-right">Rp {{ number_format($penjualan->tunai, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Kembalian</strong></td>
                        <td class="text-right"><strong>Rp {{ number_format($penjualan->kembalian, 0, ',', '.') }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <div class="card-footer">
        <a href="{{ route('transaksi.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Transaksi
        </a>
    </div>
</div>
@endsection