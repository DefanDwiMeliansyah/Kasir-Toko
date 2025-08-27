<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Faktur Pembayaran</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            margin: 0;
            padding: 10px;
        }
        .invoice {
            width: 70mm;
            margin: 0 auto;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .center {
            text-align: center;
        }
        .right {
            text-align: right;
        }
        hr {
            border: none;
            border-top: 1px solid #8c8b8b;
            margin: 5px 0;
        }
        .item-row {
            padding: 2px 0;
        }
        .discount-info {
            font-size: 10px;
            color: #666;
            font-style: italic;
        }
        .total-section {
            margin-top: 10px;
        }
        .bold {
            font-weight: bold;
        }
    </style>
</head>
<body onload="javascript:window.print()">
    <div class="invoice">
        <h3 class="center">TOKO MADU JAYA</h3>
        <p class="center">
            Jl. Raya Padaherang Km.1, Desa Padaherang <br>
            Kec. Padaherang - Kab. Pangandaran<br>
            Telp: 0812-3456-7890
        </p>
        <hr>
        
        <table>
            <tr>
                <td>No. Transaksi</td>
                <td>: {{ $penjualan->nomor_transaksi }}</td>
            </tr>
            <tr>
                <td>Tanggal</td>
                <td>: {{ date('d/m/Y H:i:s', strtotime($penjualan->tanggal)) }}</td>
            </tr>
            <tr>
                <td>Pelanggan</td>
                <td>: {{ $pelanggan->nama }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>: {{ $user->nama }}</td>
            </tr>
        </table>
        <hr>
        
        <!-- Detail Items -->
        @foreach ($detilPenjualan as $item)
        <div class="item-row">
            <table>
                <tr>
                    <td colspan="2" class="bold">{{ $item->nama_produk }}</td>
                </tr>
                <tr>
                    <td>{{ $item->jumlah }} x {{ number_format($item->harga_produk, 0, ',', '.') }}</td>
                    <td class="right">
                    @if($item->diskon_nominal > 0)
                            <span style="text-decoration: line-through; color: #6c757d;">
                                (Rp {{ number_format($item->subtotal, 0, ',', '.') }})
                            </span>
                            <strong>
                                Rp {{ number_format($item->subtotal_setelah_diskon ?? ($detail->subtotal - $detail->diskon_nominal), 0, ',', '.') }}
                            </strong>
                        @else
                            Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                        @endif
                    </td>
                </tr>
            </table>
        </div>
        <hr style="border-style: dashed;">
        @endforeach
        
        <!-- Total Section -->
        <div class="total-section">
            <table>
                <tr>
                    <td>Sub Total</td>
                    <td class="right">{{ number_format($penjualan->subtotal, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Pajak PPN (10%)</td>
                    <td class="right">{{ number_format($penjualan->pajak, 0, ',', '.') }}</td>
                </tr>
                @if($penjualan->diskon_nominal > 0)
                <tr>
                    <td>
                        Total Diskon
                        @if($diskon)
                        <br><small>({{ $diskon->kode_diskon }})</small>
                        @endif
                    </td>
                    <td class="right">-{{ number_format($penjualan->diskon_nominal, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td class="bold">TOTAL</td>
                    <td class="right bold">{{ number_format($penjualan->total, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td>Tunai</td>
                    <td class="right">{{ number_format($penjualan->tunai, 0, ',', '.') }}</td>
                </tr>
                <tr>
                    <td class="bold">Kembalian</td>
                    <td class="right bold">{{ number_format($penjualan->kembalian, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>
        
        <hr>
        
        <div class="center">
            <p><strong>TERIMA KASIH</strong><br>
            Barang yang sudah dibeli tidak dapat dikembalikan</p>
            <p>{{ date('d/m/Y H:i:s') }}</p>
        </div>
    </div>
</body>
</html>