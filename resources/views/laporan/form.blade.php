@extends('layouts.main', ['title' => 'Laporan'])
@section('title-content')
    <i class="fas fa-print mr-2"></i> Laporan
@endsection

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card">
                <div class="card-header">{{ __('Laporan') }}</div>

                <div class="card-body">
                    <div class="row">
                        <!-- Laporan Penjualan Harian -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0">Laporan Penjualan Harian</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('laporan.harian') }}" method="POST" target="_blank">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="tanggal_harian" class="form-label">Tanggal</label>
                                            <input type="date" class="form-control" id="tanggal_harian" name="tanggal" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <button type="submit" class="btn btn-primary">Generate Laporan</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Laporan Penjualan Bulanan -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Laporan Penjualan Bulanan</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('laporan.bulanan') }}" method="POST" target="_blank">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="bulan" class="form-label">Bulan</label>
                                            <select class="form-control" id="bulan" name="bulan" required>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>
                                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tahun" class="form-label">Tahun</label>
                                            <select class="form-control" id="tahun" name="tahun" required>
                                                @for($i = date('Y'); $i >= date('Y')-5; $i--)
                                                    <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-success">Generate Laporan</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Laporan Laba Rugi Harian -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Laporan Laba Rugi Harian</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('laporan.laba-rugi-harian') }}" method="POST" target="_blank">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="tanggal_laba_harian" class="form-label">Tanggal</label>
                                            <input type="date" class="form-control" id="tanggal_laba_harian" name="tanggal" value="{{ date('Y-m-d') }}" required>
                                        </div>
                                        <button type="submit" class="btn btn-info">Generate Laporan Detail</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Laporan Laba Rugi Bulanan -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-warning text-white">
                                    <h5 class="mb-0">Laporan Laba Rugi Bulanan</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('laporan.laba-rugi-bulanan') }}" method="POST" target="_blank">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="bulan_laba" class="form-label">Bulan</label>
                                            <select class="form-control" id="bulan_laba" name="bulan" required>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>
                                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tahun_laba" class="form-label">Tahun</label>
                                            <select class="form-control" id="tahun_laba" name="tahun" required>
                                                @for($i = date('Y'); $i >= date('Y')-5; $i--)
                                                    <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-warning">Generate Laporan Detail</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- FITUR BARU: Laporan Produk Terlaris -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0">Laporan Produk Terlaris</h5>
                                </div>
                                <div class="card-body">
                                    <form action="{{ route('laporan.produk-terlaris') }}" method="POST" target="_blank">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="bulan_terlaris" class="form-label">Bulan</label>
                                            <select class="form-control" id="bulan_terlaris" name="bulan" required>
                                                @for($i = 1; $i <= 12; $i++)
                                                    <option value="{{ $i }}" {{ $i == date('n') ? 'selected' : '' }}>
                                                        {{ DateTime::createFromFormat('!m', $i)->format('F') }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label for="tahun_terlaris" class="form-label">Tahun</label>
                                            <select class="form-control" id="tahun_terlaris" name="tahun" required>
                                                @for($i = date('Y'); $i >= date('Y')-5; $i--)
                                                    <option value="{{ $i }}" {{ $i == date('Y') ? 'selected' : '' }}>{{ $i }}</option>
                                                @endfor
                                            </select>
                                        </div>
                                        <button type="submit" class="btn btn-secondary">Generate Laporan</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <div class="alert alert-info">
                            <h6><strong>Keterangan:</strong></h6>
                            <ul class="mb-0">
                                <li><strong>Laporan Penjualan:</strong> Menampilkan data transaksi dan ringkasan penjualan</li>
                                <li><strong>Laporan Laba Rugi Detail:</strong> Menampilkan analisis laba rugi per produk, per hari, dan ringkasan keseluruhan</li>
                                <li><strong>Laporan Produk Terlaris:</strong> Menampilkan produk-produk dengan penjualan terbanyak, frekuensi pembelian, dan total pendapatan per produk</li>
                                <li>Semua laporan akan dibuka di tab baru untuk memudahkan pencetakan</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection