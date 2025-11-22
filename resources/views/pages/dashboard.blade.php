@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/apexcharts/dist/apexcharts.css') }}" />
<style>
    .stat-card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Dashboard"
    :breadcrumb-title="'Dashboard'"
>
    <x-slot name="action">
        <div class="btn-group">
            <button type="button" class="btn btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="ti ti-download me-1"></i> Export Laporan
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="{{ route('dashboard.export', ['format' => 'excel']) }}">
                    <i class="ti ti-file-excel me-2"></i> Export ke Excel
                </a></li>
                <li><a class="dropdown-item" href="{{ route('dashboard.export', ['format' => 'csv']) }}">
                    <i class="ti ti-file-csv me-2"></i> Export ke CSV
                </a></li>
            </ul>
        </div>
    </x-slot>
</x-layout.page-header>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="ti ti-users"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total Pelanggan Aktif</h6>
                        <h3 class="mb-0">{{ number_format($statistics['total_active_customers'] ?? 0) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-info-subtle text-info">
                            <i class="ti ti-file-invoice"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Tagihan Bulan Ini</h6>
                        <h3 class="mb-0">Rp {{ number_format($statistics['total_invoices_this_month'] ?? 0, 0, ',', '.') }}</h3>
                        <small class="text-muted">{{ $statistics['total_invoices_count_this_month'] ?? 0 }} tagihan</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-warning-subtle text-warning">
                            <i class="ti ti-alert-circle"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Tagihan Belum Dibayar</h6>
                        <h3 class="mb-0">Rp {{ number_format($statistics['total_unpaid_invoices'] ?? 0, 0, ',', '.') }}</h3>
                        <small class="text-muted">Rp {{ number_format($statistics['unpaid_invoices_this_month'] ?? 0, 0, ',', '.') }} bulan ini</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 col-sm-6 mb-3">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-danger-subtle text-danger">
                            <i class="ti ti-clock-exclamation"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Pelanggan Terlambat</h6>
                        <h3 class="mb-0">{{ number_format($statistics['customers_with_overdue'] ?? 0) }}</h3>
                        <small class="text-muted">Perlu ditindaklanjuti</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Revenue Card -->
<div class="row mb-4">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="ti ti-currency-dollar"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Pendapatan Bulan Ini</h6>
                        <h3 class="mb-0 text-success">Rp {{ number_format($statistics['total_revenue_this_month'] ?? 0, 0, ',', '.') }}</h3>
                        <small class="text-muted">Dari tagihan yang sudah dibayar</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Distribusi Status Tagihan</h5>
                <div class="row text-center">
                    <div class="col-4">
                        <div class="p-3 bg-success-subtle rounded">
                            <h4 class="text-success mb-1">{{ number_format($statusDistribution['paid'] ?? 0) }}</h4>
                            <small class="text-muted">Sudah Dibayar</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-warning-subtle rounded">
                            <h4 class="text-warning mb-1">{{ number_format($statusDistribution['unpaid'] ?? 0) }}</h4>
                            <small class="text-muted">Belum Dibayar</small>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-3 bg-danger-subtle rounded">
                            <h4 class="text-danger mb-1">{{ number_format($statusDistribution['overdue'] ?? 0) }}</h4>
                            <small class="text-muted">Terlambat</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Charts Row -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Grafik Pendapatan Bulanan</h5>
                    <span class="badge bg-primary">12 Bulan Terakhir</span>
                </div>
                <div id="monthly-revenue-chart" style="min-height: 350px;"></div>
      </div>
    </div>
  </div>
</div>

<!-- Recent Payments -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Pembayaran Terbaru</h5>
                    <a href="{{ route('invoices.index') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua
                    </a>
                </div>
                @if(!empty($recentPayments) && count($recentPayments) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>No. Tagihan</th>
                                    <th>Pelanggan</th>
                                    <th>Metode</th>
                                    <th>Jumlah</th>
                                    <th>Tanggal</th>
                                    <th>Diproses Oleh</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPayments as $payment)
                                    <tr>
                                        <td>{{ $payment->invoice->invoice_number ?? '-' }}</td>
                                        <td>{{ $payment->customer->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ strtoupper($payment->method) }}</span>
                                        </td>
                                        <td>Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                                        <td>{{ $payment->paid_date ? \Carbon\Carbon::parse($payment->paid_date)->format('d/m/Y') : '-' }}</td>
                                        <td>{{ $payment->receivedBy->name ?? '-' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ti ti-file-off fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Belum ada data pembayaran</p>
                    </div>
                @endif
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script src="{{ asset('assets/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Monthly Revenue Chart
    const monthlyRevenueData = @json($monthlyRevenue);
    
    const revenueChartOptions = {
        series: [{
            name: 'Pendapatan',
            data: monthlyRevenueData.data
        }],
        chart: {
            type: 'area',
            height: 350,
            fontFamily: 'inherit',
            toolbar: {
                show: true,
                tools: {
                    download: true,
                    selection: true,
                    zoom: true,
                    zoomin: true,
                    zoomout: true,
                    pan: true,
                    reset: true
                }
            }
        },
        colors: ['var(--bs-primary)'],
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 3
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 0,
                inverseColors: false,
                opacityFrom: 0.5,
                opacityTo: 0.1,
                stops: [0, 100]
            }
        },
        xaxis: {
            categories: monthlyRevenueData.labels,
            labels: {
                style: {
                    colors: '#adb0bb'
                }
            }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#adb0bb'
                },
                formatter: function(val) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                }
            }
        },
        tooltip: {
            theme: 'dark',
            y: {
                formatter: function(val) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                }
            }
        },
        grid: {
            borderColor: 'rgba(0,0,0,0.05)',
            strokeDashArray: 4
        }
    };

    const revenueChart = new ApexCharts(document.querySelector("#monthly-revenue-chart"), revenueChartOptions);
    revenueChart.render();
});
</script>
@endpush
@endsection
