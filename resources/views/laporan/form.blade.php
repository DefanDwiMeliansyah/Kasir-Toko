@extends('layouts.main', ['title' => 'Laporan'])
@section('title-content')
    <i class="fas fa-print mr-2"></i> Laporan
@endsection

@section('content')
<div class="row">

    {{-- Laporan Harian --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <form target="_blank" method="GET" action="{{ route('laporan.harian') }}">
            <div class="card card-orange card-outline h-100">
                <div class="card-header">
                    <h3 class="card-title">Buat Laporan Harian</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control">
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-print mr-2"></i> Cetak
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Laporan Bulanan --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <form target="_blank" method="GET" action="{{ route('laporan.bulanan') }}">
            <div class="card card-orange card-outline h-100">
                <div class="card-header">
                    <h3 class="card-title">Buat Laporan Bulanan</h3>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col">
                            <label>Bulan</label>
                            <?php
                            $pilihan = [
                                'Pilih Bulan',
                                'Januari',
                                'Februari',
                                'Maret',
                                'April',
                                'Mei',
                                'Juni',
                                'Juli',
                                'Agustus',
                                'September',
                                'Oktober',
                                'November',
                                'Desember'
                            ];
                            ?>
                            <select name="bulan" class="form-control">
                                @foreach ($pilihan as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                <option value="">Pilih Tahun</option>
                                @for ($t = date('Y'); $t > date('Y') - 5; $t--)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-print mr-2"></i> Cetak
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Laporan Laba Rugi Harian --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <form target="_blank" method="GET" action="{{ route('laporan.laba-rugi-harian') }}">
            <div class="card card-orange card-outline h-100">
                <div class="card-header">
                    <h3 class="card-title">Laporan Laba Rugi Harian</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control">
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-chart-line mr-2"></i> Cetak
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Laporan Laba Rugi Bulanan --}}
    <div class="col-md-6 col-xl-4 mb-3">
        <form target="_blank" method="GET" action="{{ route('laporan.laba-rugi-bulanan') }}">
            <div class="card card-orange card-outline h-100">
                <div class="card-header">
                    <h3 class="card-title">Laporan Laba Rugi Bulanan</h3>
                </div>
                <div class="card-body">
                    <div class="form-group row">
                        <div class="col">
                            <label>Bulan</label>
                            <select name="bulan" class="form-control">
                                @foreach ($pilihan as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col">
                            <label>Tahun</label>
                            <select name="tahun" class="form-control">
                                <option value="">Pilih Tahun</option>
                                @for ($t = date('Y'); $t > date('Y') - 5; $t--)
                                    <option value="{{ $t }}">{{ $t }}</option>
                                @endfor
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-footer text-right">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-chart-line mr-2"></i> Cetak
                    </button>
                </div>
            </div>
        </form>
    </div>

</div>
@endsection
