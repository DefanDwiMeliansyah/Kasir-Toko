@extends('layouts.main', ['title' => 'Invoice'])
@section('title-content')
<i class="fas fa-file-invoice mr-2"></i> Invoice
@endsection

@section('content')
@if (session('destroy') == 'success')
    <x-alert type="success">
        <strong>Berhasil dibatalkan!</strong> Transaksi berhasil dibatalkan.
    </x-alert>
@endif

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
                    <td>
                        {{ $detail->nama_produk }}
                        @if($detail->diskon_nominal > 0)
                            <br><small class="text-success">
                                <i class="fas fa-tag mr-1"></i>
                                @if($diskon)
                                    Diskon {{ $diskon->kode_diskon }}:
                                @else
                                    Diskon:
                                @endif
                                -Rp {{ number_format($detail->diskon_nominal, 0, ',', '.') }}
                            </small>
                        @endif
                    </td>
                    <td>{{ $detail->jumlah }}</td>
                    <td>Rp {{ number_format($detail->harga_produk, 0, ',', '.') }}</td>
                    <td>
                        @if($detail->diskon_nominal > 0)
                            <span style="text-decoration: line-through; color: #6c757d;">
                                Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                            </span><br>
                            <strong class="text-success">
                                Rp {{ number_format($detail->subtotal_setelah_diskon ?? ($detail->subtotal - $detail->diskon_nominal), 0, ',', '.') }}
                            </strong>
                        @else
                            Rp {{ number_format($detail->subtotal, 0, ',', '.') }}
                        @endif
                    </td>
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
                            Total Diskon
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

        @if($penjualan->diskon_nominal > 0 && $diskon)
        <div class="row mt-3">
            <div class="col-12">
                <div class="alert alert-success">
                    <h6><i class="fas fa-tags mr-2"></i>Informasi Diskon</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Kode:</strong> {{ $diskon->kode_diskon }}<br>
                            <strong>Nama:</strong> {{ $diskon->nama_diskon }}<br>
                            @if($diskon->deskripsi)
                                <strong>Deskripsi:</strong> {{ $diskon->deskripsi }}
                            @endif
                        </div>
                        <div class="col-md-6 text-right">
                            <h5 class="text-success mb-0">
                                Total Hemat: Rp {{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>


<div class="card-footer form-inline">
    <a href="{{ route('transaksi.index') }}" class="btn btn-secondary mr-2">Ke Transaksi</a>
    @if ($penjualan->status == 'selesai')
        <button type="button" class="btn btn-danger ml-auto mr-2" data-toggle="modal" data-target="#modalBatal">Dibatalkan</button>
    @endif
    <a target="_blank" href="{{ route('transaksi.cetak', ['transaksi' => $penjualan->id]) }}" class="btn btn-primary @if ($penjualan->status == 'batal') ml-auto @endif">
        <i class="fas fa-print mr-2"></i> Cetak
    </a>
</div>
@endsection

@push('modals')
<div class="modal fade" id="modalBatal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dibatalkan</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>Apakah yakin akan dibatalkan?</p>
                <form action="{{ route('transaksi.destroy', ['transaksi' => $penjualan->id]) }}" method="post" style="display: none;" id="formBatal">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-danger" id="yesBatal">Ya, Batalkan!</button>
            </div>
        </div>
    </div>
</div>
@endpush
@push('scripts')
<script>
    $(function() {
        // Event untuk submit form saat konfirmasi
        $('#yesBatal').click(function() {
            $('#formBatal').submit();
        });

        // Ambil waktu transaksi dari PHP ke JavaScript
        const waktuTransaksi = new Date("{{ $penjualan->tanggal }}");
        const waktuSekarang = new Date();

        // Hitung waktu 3 jam setelah transaksi
        const batasPembatalan = new Date(waktuTransaksi.getTime() + 3 * 60 * 1000); // 3 menit untuk testing

        // Jika waktu sekarang sudah lebih dari 3 jam dari waktu transaksi
        if (waktuSekarang > batasPembatalan) {
            // Disable tombol batal
            $('[data-target="#modalBatal"]').prop('disabled', true).text('Batal (Expired)').addClass('disabled');
        }
    });
</script>
@endpush