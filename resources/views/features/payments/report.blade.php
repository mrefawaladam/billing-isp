@extends('layouts.app')

@section('title', 'Laporan Pembayaran')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="{{ asset('assets/libs/apexcharts/apexcharts.css') }}" />
<style>
    .stat-card {
        border-left: 4px solid;
    }
    .stat-card.primary { border-left-color: #5d87ff; }
    .stat-card.success { border-left-color: #13deb9; }
    .stat-card.warning { border-left-color: #ffae1f; }
    .stat-card.info { border-left-color: #539bff; }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Laporan Pembayaran"
    :breadcrumb-title="'Laporan Pembayaran'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<div class="container-fluid">
    <!-- Filter Section -->
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-filter me-2"></i>Filter Laporan</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('payments.report') }}" id="filter-form">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" name="start_date" value="{{ $filters['start_date'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" name="end_date" value="{{ $filters['end_date'] }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Customer</label>
                        <select class="form-select" name="customer_id">
                            <option value="">Semua Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ $filters['customer_id'] == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }} ({{ $customer->customer_code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Metode Pembayaran</label>
                        <select class="form-select" name="method">
                            <option value="">Semua Metode</option>
                            <option value="cash" {{ $filters['method'] == 'cash' ? 'selected' : '' }}>Cash</option>
                            <option value="transfer" {{ $filters['method'] == 'transfer' ? 'selected' : '' }}>Transfer</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Diterima Oleh</label>
                        <select class="form-select" name="received_by">
                            <option value="">Semua Staff</option>
                            @foreach($staff as $user)
                                <option value="{{ $user->id }}" {{ $filters['received_by'] == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="ti ti-search me-1"></i> Filter
                        </button>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="{{ route('payments.report') }}" class="btn btn-secondary w-100">
                            <i class="ti ti-refresh me-1"></i> Reset
                        </a>
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <a href="{{ route('payments.report.export', $filters) }}" class="btn btn-success w-100">
                            <i class="ti ti-file-export me-1"></i> Export CSV
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card stat-card primary">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Total Pembayaran</h6>
                            <h4 class="mb-0">Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</h4>
                        </div>
                        <div class="flex-shrink-0">
                            <iconify-icon icon="solar:wallet-money-line-duotone" class="fs-1 text-primary"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card success">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Jumlah Transaksi</h6>
                            <h4 class="mb-0">{{ number_format($summary['total_count'], 0, ',', '.') }}</h4>
                        </div>
                        <div class="flex-shrink-0">
                            <iconify-icon icon="solar:document-text-line-duotone" class="fs-1 text-success"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card warning">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Cash</h6>
                            <h4 class="mb-0">Rp {{ number_format($summary['cash_amount'], 0, ',', '.') }}</h4>
                            <small class="text-muted">{{ $summary['cash_count'] }} transaksi</small>
                        </div>
                        <div class="flex-shrink-0">
                            <iconify-icon icon="solar:wallet-line-duotone" class="fs-1 text-warning"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card stat-card info">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="text-muted mb-1">Transfer</h6>
                            <h4 class="mb-0">Rp {{ number_format($summary['transfer_amount'], 0, ',', '.') }}</h4>
                            <small class="text-muted">{{ $summary['transfer_count'] }} transaksi</small>
                        </div>
                        <div class="flex-shrink-0">
                            <iconify-icon icon="solar:card-transfer-line-duotone" class="fs-1 text-info"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Daily Revenue Chart -->
    @if(count($daily_revenue['labels']) > 0)
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="ti ti-chart-line me-2"></i>Grafik Pendapatan Harian</h5>
        </div>
        <div class="card-body">
            <div id="daily-revenue-chart"></div>
        </div>
    </div>
    @endif

    <!-- Payments Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="ti ti-list-check me-2"></i>Detail Pembayaran</h5>
            <span class="badge bg-primary">{{ $payments->count() }} transaksi</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="payments-table" class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>No. Invoice</th>
                            <th>Customer</th>
                            <th>Metode</th>
                            <th>Jumlah</th>
                            <th>Diterima Oleh</th>
                            <th>Bukti Transfer</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td>{{ $payment->paid_date->format('d/m/Y') }}</td>
                            <td>
                                <a href="{{ route('invoices.show', $payment->invoice_id) }}" class="text-primary">
                                    {{ $payment->invoice->invoice_number ?? '-' }}
                                </a>
                            </td>
                            <td>
                                <a href="{{ route('customers.show', $payment->customer_id) }}" class="text-primary">
                                    {{ $payment->customer->name ?? '-' }}
                                </a>
                                <br><small class="text-muted">{{ $payment->customer->customer_code ?? '-' }}</small>
                            </td>
                            <td>
                                @if($payment->method === 'cash')
                                    <span class="badge bg-warning">Cash</span>
                                @else
                                    <span class="badge bg-info">Transfer</span>
                                @endif
                            </td>
                            <td><strong>Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong></td>
                            <td>
                                {{ $payment->receivedBy->name ?? '-' }}
                                @if($payment->receivedBy)
                                    <br><small class="text-muted">{{ $payment->receivedBy->email ?? '' }}</small>
                                @endif
                            </td>
                            <td>
                                @if($payment->transfer_proof_url)
                                    <a href="{{ $payment->transfer_proof_url }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="ti ti-photo me-1"></i>Lihat Bukti
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>{{ $payment->note ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($payments->count() > 0)
            <div class="card-footer bg-light">
                <div class="row">
                    <div class="col-md-12 text-end">
                        <h5 class="mb-0">
                            <strong>TOTAL: Rp {{ number_format($summary['total_amount'], 0, ',', '.') }}</strong>
                        </h5>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/apexcharts/apexcharts.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Initialize DataTable
    if ($.fn.DataTable.isDataTable('#payments-table')) {
        $('#payments-table').DataTable().destroy();
    }

    $('#payments-table').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        searching: true,
        paging: {{ $payments->count() > 0 ? 'true' : 'false' }},
        info: {{ $payments->count() > 0 ? 'true' : 'false' }},
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            lengthMenu: "Tampilkan _MENU_ entri",
            zeroRecords: "Tidak ada data pembayaran untuk periode ini.",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ entri",
            infoEmpty: "Menampilkan 0 sampai 0 dari 0 entri",
            infoFiltered: "(difilter dari _MAX_ total entri)",
            search: "Cari:",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

    // Daily Revenue Chart
    @if(count($daily_revenue['labels']) > 0)
    var dailyRevenueOptions = {
        series: [{
            name: 'Pendapatan',
            data: @json($daily_revenue['data'])
        }],
        chart: {
            type: 'area',
            height: 350,
            toolbar: {
                show: true
            }
        },
        dataLabels: {
            enabled: false
        },
        stroke: {
            curve: 'smooth',
            width: 2
        },
        xaxis: {
            categories: @json($daily_revenue['labels']),
            labels: {
                format: 'dd/MM'
            }
        },
        yaxis: {
            labels: {
                formatter: function(val) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                }
            }
        },
        tooltip: {
            y: {
                formatter: function(val) {
                    return 'Rp ' + new Intl.NumberFormat('id-ID').format(val);
                }
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shadeIntensity: 1,
                opacityFrom: 0.7,
                opacityTo: 0.9,
                stops: [0, 90, 100]
            }
        },
        colors: ['#5d87ff']
    };

    var dailyRevenueChart = new ApexCharts(document.querySelector("#daily-revenue-chart"), dailyRevenueOptions);
    dailyRevenueChart.render();
    @endif
});
</script>
@endpush
@endsection

