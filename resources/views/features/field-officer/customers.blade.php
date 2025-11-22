@extends('layouts.app')

@section('title', 'Daftar Pelanggan')

@push('styles')
<style>
    .customer-card {
        transition: transform 0.2s, box-shadow 0.2s;
        cursor: pointer;
    }
    .customer-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    @media (max-width: 768px) {
        .customer-card {
            margin-bottom: 1rem;
        }
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Daftar Pelanggan"
    :breadcrumb-title="'Daftar Pelanggan'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('field-officer.customers') }}" id="search-form">
            <div class="row g-3">
                <div class="col-md-10">
                    <input type="text"
                           name="search"
                           class="form-control"
                           placeholder="Cari nama, kode, atau telepon pelanggan..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="ti ti-search me-1"></i> Cari
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Customers List -->
@if($customers->count() > 0)
    <div class="row">
        @foreach($customers as $customer)
            @php
                $latestInvoice = $customer->invoices->first();
                $statusBadge = 'bg-secondary';
                $statusText = 'Belum Ada Tagihan';
                $statusColor = 'text-secondary';

                if ($latestInvoice) {
                    switch($latestInvoice->status) {
                        case 'PAID':
                            $statusBadge = 'bg-success';
                            $statusText = 'Sudah Dibayar';
                            $statusColor = 'text-success';
                            break;
                        case 'OVERDUE':
                            $statusBadge = 'bg-danger';
                            $statusText = 'Terlambat';
                            $statusColor = 'text-danger';
                            break;
                        case 'UNPAID':
                            $statusBadge = 'bg-warning';
                            $statusText = 'Belum Dibayar';
                            $statusColor = 'text-warning';
                            break;
                    }
                }
            @endphp
            <div class="col-md-6 col-lg-4 mb-3">
                <div class="card customer-card h-100" onclick="window.location.href='{{ route('field-officer.customers.show', $customer) }}'">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0">{{ $customer->name }}</h5>
                            <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                        </div>
                        <p class="text-muted small mb-2">
                            <i class="ti ti-code me-1"></i> {{ $customer->customer_code ?? '-' }}
                        </p>
                        <p class="text-muted small mb-2">
                            <i class="ti ti-phone me-1"></i> {{ $customer->phone ?? '-' }}
                        </p>
                        @if($latestInvoice)
                            <div class="mt-3 pt-3 border-top">
                                <p class="small mb-1">
                                    <strong>Tagihan Terakhir:</strong><br>
                                    {{ $latestInvoice->invoice_number }}
                                </p>
                                <p class="small mb-1">
                                    <strong>Jatuh Tempo:</strong>
                                    {{ $latestInvoice->due_date ? $latestInvoice->due_date->format('d/m/Y') : '-' }}
                                </p>
                                <p class="small mb-0">
                                    <strong>Total:</strong>
                                    <span class="fw-bold">Rp {{ number_format($latestInvoice->total_amount, 0, ',', '.') }}</span>
                                </p>
                            </div>
                        @endif
                        <div class="mt-3">
                            <a href="{{ route('field-officer.customers.show', $customer) }}" class="btn btn-sm btn-primary w-100">
                                <i class="ti ti-eye me-1"></i> Detail
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-4">
        {{ $customers->links() }}
    </div>
@else
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="ti ti-users-off fs-1 text-muted"></i>
            <p class="text-muted mt-3">
                @if(request('search'))
                    Tidak ada pelanggan yang sesuai dengan pencarian Anda.
                @else
                    Belum ada pelanggan yang ditugaskan kepada Anda.
                @endif
            </p>
            @if(request('search'))
                <a href="{{ route('field-officer.customers') }}" class="btn btn-primary">
                    <i class="ti ti-refresh me-1"></i> Reset Pencarian
                </a>
            @endif
        </div>
    </div>
@endif
@endsection

