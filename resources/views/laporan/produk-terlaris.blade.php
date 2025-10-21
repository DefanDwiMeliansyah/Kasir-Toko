<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Produk Terlaris - {{ $bulan }} {{ $tahun }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .company-info {
            margin-bottom: 10px;
        }
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin: 10px 0;
        }
        .report-period {
            font-size: 14px;
            color: #666;
        }
        .summary-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .summary-title {
            font-weight: bold;
            margin-bottom: 10px;
            color: #495057;
        }
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .ranking {
            font-weight: bold;
            color: #495057;
        }
        .top-3 {
            background-color: #fff3cd;
        }
        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            display: flex;
            justify-content: space-between;
        }
        .print-info {
            font-size: 10px;
            color: #666;
        }
        @media print {
            body { margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-info">
            <h2>{{ config('app.name', 'Sistem POS') }}</h2>
            <p>Alamat Perusahaan | Telp: (021) 1234567 | Email: info@example.com</p>
        </div>
        <div class="report-title">LAPORAN PRODUK TERLARIS</div>
        <div class="report-period">Periode: {{ $bulan }} {{ $tahun }}</div>
    </div>

    <!-- Ringkasan Keseluruhan -->
    <div class="summary-box">
        <div class="summary-title">RINGKASAN KESELURUHAN</div>
        <div class="summary-row">
            <span>Total Jenis Produk Terjual:</span>
            <span><strong>{{ number_format($ringkasan->total_produk_terjual) }} produk</strong></span>
        </div>
        <div class="summary-row">
            <span>Total Kuantitas Terjual:</span>
            <span><strong>{{ number_format($ringkasan->total_kuantitas) }} unit</strong></span>
        </div>
        <div class="summary-row">
            <span>Total Transaksi:</span>
            <span><strong>{{ number_format($ringkasan->total_transaksi) }} transaksi</strong></span>
        </div>
        <div class="summary-row">
            <span>Total Pendapatan:</span>
            <span><strong>Rp {{ number_format($ringkasan->total_pendapatan, 0, ',', '.') }}</strong></span>
        </div>
    </div>

    <!-- Tabel Detail Produk Terlaris -->
    <table>
        <thead>
            <tr>
                <th width="5%">Rank</th>
                <th width="12%">Kode Produk</th>
                <th width="25%">Nama Produk</th>
                <th width="15%">Kategori</th>
                <th width="10%">Harga</th>
                <th width="8%">Qty Terjual</th>
                <th width="8%">Frekuensi</th>
                <th width="12%">Total Pendapatan</th>
                <th width="5%">%</th>
            </tr>
        </thead>
        <tbody>
            @forelse($produkTerlaris as $index => $produk)
                <tr class="{{ $index < 3 ? 'top-3' : '' }}">
                    <td class="text-center ranking">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $produk->kode_produk }}</td>
                    <td>{{ $produk->nama_produk }}</td>
                    <td class="text-center">{{ $produk->nama_kategori }}</td>
                    <td class="text-right">Rp {{ number_format($produk->harga, 0, ',', '.') }}</td>
                    <td class="text-center"><strong>{{ number_format($produk->total_terjual) }}</strong></td>
                    <td class="text-center">{{ number_format($produk->frekuensi_pembelian) }}x</td>
                    <td class="text-right">Rp {{ number_format($produk->total_pendapatan, 0, ',', '.') }}</td>
                    <td class="text-center">
                        {{ $ringkasan->total_kuantitas > 0 ? number_format(($produk->total_terjual / $ringkasan->total_kuantitas) * 100, 1) : 0 }}%
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px; color: #666;">
                        Tidak ada data penjualan untuk periode {{ $bulan }} {{ $tahun }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if($produkTerlaris->count() > 0)
    <!-- Analisis Top 3 -->
    <div class="summary-box">
        <div class="summary-title">ANALISIS TOP 3 PRODUK TERLARIS</div>
        @foreach($produkTerlaris->take(3) as $index => $produk)
            <div style="margin: 10px 0; padding: 8px; background: white; border-left: 4px solid #007bff;">
                <strong>{{ $index + 1 }}. {{ $produk->nama_produk }}</strong><br>
                <small>
                    Terjual {{ number_format($produk->total_terjual) }} unit dalam {{ number_format($produk->frekuensi_pembelian) }} transaksi, 
                    menghasilkan pendapatan Rp {{ number_format($produk->total_pendapatan, 0, ',', '.') }}
                    ({{ $ringkasan->total_kuantitas > 0 ? number_format(($produk->total_terjual / $ringkasan->total_kuantitas) * 100, 1) : 0 }}% dari total penjualan)
                </small>
            </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <div class="print-info">
            Dicetak pada: {{ date('d/m/Y H:i:s') }}<br>
            Operator: {{ Auth::user()->nama ?? 'System' }}
        </div>
        <div class="print-info">
            Laporan Produk Terlaris<br>
            {{ $bulan }} {{ $tahun }}
        </div>
    </div>

    <script>
        // Auto print ketika halaman dibuka
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>