@extends('layouts.main', ['title' => 'Home'])
@section('title-content')
    <i class="fas fa-home mr-2"></i> Home
@endsection

@section('content')
<div class="row">
    @can('admin')
        <x-box title="User" icon="fas fa-user-tie" background="bg-danger"
            :route="route('user.index')" :jumlah="$user->jumlah" />
        <x-box title="Kategori" icon="fas fa-list" background="bg-info"
            :route="route('kategori.index')" :jumlah="$kategori->jumlah" />
    @endcan
    <x-box title="Pelanggan" icon="fas fa-users" background="bg-primary"
        :route="route('pelanggan.index')" :jumlah="$pelanggan->jumlah" />
    <x-box title="Produk" icon="fas fa-box-open" background="bg-success"
        :route="route('produk.index')" :jumlah="$produk->jumlah" />
    <x-box title="Expired" icon="fas fa-ban" background="bg-warning"
    :route="route('expired.index')" :jumlah="\App\Models\Expired::count()" />
</div>

<!-- TAMBAHAN: Chart dalam row terpisah -->
<div class="row">
    <!-- Chart Penjualan (Existing) -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-area mr-2"></i>
                    Grafik Penjualan
                </h3>
            </div>
            <div class="card-body">
                <canvas id="myChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Chart Laba Rugi (New) -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-line mr-2"></i>
                    Grafik Laba Rugi
                </h3>
            </div>
            <div class="card-body">
                <canvas id="labaRugiChart"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Chart Penjualan (existing)
    var ctx = document.getElementById('myChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= $cart['labels'] ?>,
            datasets: [{
                label: "{{ $cart['label'] }}",
                data: <?= $cart['data'] ?>,
                borderWidth: 3,
                borderColor: 'rgb(54, 162, 235)',
                backgroundColor: 'rgba(54, 162, 235, 0.1)',
                fill: true
            }]
        },
        options: {
            responsive: true,
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });

    // Chart Laba Rugi (new)
var ctxLabaRugi = document.getElementById('labaRugiChart').getContext('2d');
var labaRugiChart = new Chart(ctxLabaRugi, {
    type: 'bar',
    data: {
        labels: {!! $chartLabaRugi['labels'] !!},
        datasets: [{
            label: "{{ $chartLabaRugi['label'] }}",
            data: {!! $chartLabaRugi['data'] !!},
            backgroundColor: function(context) {
                const value = context.raw;
                return value >= 0 ? 'rgba(40, 167, 69, 0.8)' : 'rgba(220, 53, 69, 0.8)';
            },
            borderColor: function(context) {
                const value = context.raw;
                return value >= 0 ? 'rgb(40, 167, 69)' : 'rgb(220, 53, 69)';
            },
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + value.toLocaleString('id-ID');
                    }
                }
            }
        },
        plugins: {
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Laba: Rp ' + context.raw.toLocaleString('id-ID');
                    }
                }
            }
        }
    }
});

</script>
@endpush