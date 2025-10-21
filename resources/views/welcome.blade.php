@extends('layouts.main', ['title' => 'Home'])
@section('title-content')
    <i class="fas fa-home mr-2"></i> Home
@endsection

@section('content')
<div class="row">
    @can('admin')
        <x-box title="User" icon="fas fa-user-tie" background="bg-danger"
            :route="route('user.index')" :jumlah="$user->jumlah ?? 0" />
        <x-box title="Kategori" icon="fas fa-list" background="bg-info"
            :route="route('kategori.index')" :jumlah="$kategori->jumlah ?? 0" />
    @endcan
    <x-box title="Pelanggan" icon="fas fa-users" background="bg-primary"
        :route="route('pelanggan.index')" :jumlah="$pelanggan->jumlah ?? 0" />
    <x-box title="Produk" icon="fas fa-box-open" background="bg-success"
        :route="route('produk.index')" :jumlah="$produk->jumlah ?? 0" />
    <x-box title="Expired" icon="fas fa-ban" background="bg-warning"
    :route="route('expired.index')" :jumlah="\App\Models\Expired::count()" />
</div>

<!-- Chart dalam row terpisah -->
<div class="row">
    <!-- Chart Penjualan (Existing) -->
    <div class="col-lg-12">
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

    <!-- Chart Laba Rugi (Existing) -->
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

    <!-- FITUR BARU: Chart Produk Terlaris -->
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie mr-2"></i>
                    Produk Terlaris
                </h3>
            </div>
            <div class="card-body">
                <canvas id="produkTerlarisChart"></canvas>
                @if(isset($chartProdukTerlaris['data']) && count(json_decode($chartProdukTerlaris['data'], true)) == 0)
                    <div class="text-center text-muted mt-3">
                        <p>Tidak ada data penjualan bulan ini</p>
                    </div>
                @endif
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
            labels: {!! $cart['labels'] ?? '[]' !!},
            datasets: [{
                label: "{{ $cart['label'] ?? 'Penjualan' }}",
                data: {!! $cart['data'] ?? '[]' !!},
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

    // Chart Laba Rugi (existing)
    var ctxLabaRugi = document.getElementById('labaRugiChart').getContext('2d');
    var labaRugiChart = new Chart(ctxLabaRugi, {
        type: 'bar',
        data: {
            labels: {!! $chartLabaRugi['labels'] ?? '[]' !!},
            datasets: [{
                label: "{{ $chartLabaRugi['label'] ?? 'Laba Rugi' }}",
                data: {!! $chartLabaRugi['data'] ?? '[]' !!},
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

    // FITUR BARU: Chart Produk Terlaris (Donut Chart) - DENGAN ERROR HANDLING
    var ctxProdukTerlaris = document.getElementById('produkTerlarisChart').getContext('2d');
    
    @if(isset($chartProdukTerlaris) && isset($chartProdukTerlaris['data']))
        var chartLabels = {!! $chartProdukTerlaris['labels'] ?? '[]' !!};
        var chartData = {!! $chartProdukTerlaris['data'] ?? '[]' !!};
        var chartColors = {!! $chartProdukTerlaris['colors'] ?? '["#FF6384", "#36A2EB", "#FFCE56", "#4BC0C0", "#9966FF"]' !!};
        
        if (chartData.length > 0) {
            var produkTerlarisChart = new Chart(ctxProdukTerlaris, {
                type: 'doughnut',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: "{{ $chartProdukTerlaris['label'] ?? 'Produk Terlaris' }}",
                        data: chartData,
                        backgroundColor: chartColors,
                        borderColor: chartColors,
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                padding: 20,
                                usePointStyle: true,
                                font: {
                                    size: 11
                                },
                                generateLabels: function(chart) {
                                    const data = chart.data;
                                    if (data.labels.length && data.datasets.length) {
                                        const dataset = data.datasets[0];
                                        const total = dataset.data.reduce((a, b) => a + b, 0);
                                        
                                        return data.labels.map((label, index) => {
                                            const value = dataset.data[index];
                                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                            
                                            return {
                                                text: `${label}: ${value} (${percentage}%)`,
                                                fillStyle: dataset.backgroundColor[index],
                                                strokeStyle: dataset.borderColor[index],
                                                lineWidth: dataset.borderWidth,
                                                pointStyle: 'circle',
                                                hidden: false,
                                                index: index
                                            };
                                        });
                                    }
                                    return [];
                                }
                            }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                                    return `${label}: ${value} unit (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        } else {
            // Jika tidak ada data, tampilkan pesan
            ctxProdukTerlaris.fillStyle = '#999';
            ctxProdukTerlaris.font = '14px Arial';
            ctxProdukTerlaris.textAlign = 'center';
            ctxProdukTerlaris.fillText('Tidak ada data', 200, 150);
        }
    @else
        // Fallback jika variabel tidak ada
        ctxProdukTerlaris.fillStyle = '#999';
        ctxProdukTerlaris.font = '14px Arial';
        ctxProdukTerlaris.textAlign = 'center';
        ctxProdukTerlaris.fillText('Chart tidak tersedia', 200, 150);
    @endif

</script>
@endpush