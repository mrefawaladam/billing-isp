@extends('layouts.app')

@section('title', 'Dashboard Staff')

@push('styles')
<style>
    .stat-card {
        transition: transform 0.2s;
    }
    .stat-card:hover {
        transform: translateY(-5px);
    }
    @media (max-width: 768px) {
        .stat-card {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Dashboard Staff"
    :breadcrumb-title="'Dashboard Staff'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Statistics Cards -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-primary-subtle rounded p-3">
                            <i class="ti ti-users text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Total Pelanggan</h6>
                        <h3 class="mb-0">{{ $totalCustomers }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-warning-subtle rounded p-3">
                            <i class="ti ti-alert-circle text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Tagihan Belum Dibayar</h6>
                        <h3 class="mb-0">{{ $unpaidInvoices }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="bg-success-subtle rounded p-3">
                            <i class="ti ti-check text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <h6 class="text-muted mb-1">Dibayar Hari Ini</h6>
                        <h3 class="mb-0">{{ $paidToday }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Aksi Cepat</h5>
                <div class="row g-3">
                    <div class="col-md-4 col-sm-6">
                        <a href="{{ route('field-officer.customers') }}" class="btn btn-primary w-100">
                            <i class="ti ti-list me-2"></i> Daftar Pelanggan
                        </a>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <a href="{{ route('field-officer.map') }}" class="btn btn-info w-100">
                            <i class="ti ti-map me-2"></i> Lihat Peta
                        </a>
                    </div>
                    <div class="col-md-4 col-sm-6">
                        <a href="{{ route('field-officer.customers') }}?filter=unpaid" class="btn btn-warning w-100">
                            <i class="ti ti-alert-circle me-2"></i> Tagihan Belum Dibayar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Customers -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Pelanggan Saya</h5>
                    <a href="{{ route('field-officer.customers') }}" class="btn btn-sm btn-outline-primary">
                        Lihat Semua
                    </a>
                </div>
                @if($customers->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Nama</th>
                                    <th>Kode</th>
                                    <th>Telepon</th>
                                    <th>Status Tagihan</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customers->take(10) as $customer)
                                    @php
                                        $latestInvoice = $customer->invoices->first();
                                        $statusBadge = 'bg-secondary';
                                        $statusText = 'Belum Ada Tagihan';

                                        if ($latestInvoice) {
                                            switch($latestInvoice->status) {
                                                case 'PAID':
                                                    $statusBadge = 'bg-success';
                                                    $statusText = 'Sudah Dibayar';
                                                    break;
                                                case 'OVERDUE':
                                                    $statusBadge = 'bg-danger';
                                                    $statusText = 'Terlambat';
                                                    break;
                                                case 'UNPAID':
                                                    $statusBadge = 'bg-warning';
                                                    $statusText = 'Belum Dibayar';
                                                    break;
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $customer->name }}</td>
                                        <td>{{ $customer->customer_code ?? '-' }}</td>
                                        <td>{{ $customer->phone ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                                        </td>
                                        <td>
                                            <a href="{{ route('field-officer.customers.show', $customer) }}" class="btn btn-sm btn-primary">
                                                <i class="ti ti-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ti ti-users-off fs-1 text-muted"></i>
                        <p class="text-muted mt-2">Belum ada pelanggan yang ditugaskan kepada Anda.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

