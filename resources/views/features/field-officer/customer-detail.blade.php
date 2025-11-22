@extends('layouts.app')

@section('title', 'Detail Pelanggan')

@push('styles')
<style>
    .invoice-card {
        transition: transform 0.2s;
    }
    .invoice-card:hover {
        transform: translateX(5px);
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Detail Pelanggan"
    :breadcrumb-title="'Detail Pelanggan'"
    :breadcrumb-items="[
        ['label' => 'Daftar Pelanggan', 'url' => route('field-officer.customers')],
        ['label' => $customer->name]
    ]"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Customer Info -->
<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5 class="card-title mb-3">{{ $customer->name }}</h5>
                <table class="table table-borderless">
                    <tr>
                        <td width="150"><strong>Kode Pelanggan:</strong></td>
                        <td>{{ $customer->customer_code ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Telepon:</strong></td>
                        <td>{{ $customer->phone ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Alamat:</strong></td>
                        <td>{{ $customer->address ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Paket Bulanan:</strong></td>
                        <td>Rp {{ number_format($customer->total_fee, 0, ',', '.') }}</td>
                    </tr>
                </table>
            </div>
            <div class="col-md-6">
                @if($customer->lat && $customer->lng)
                    <div id="customer-map" style="height: 200px; border-radius: 8px; overflow: hidden;"></div>
                @else
                    <p class="text-muted">Lokasi tidak tersedia</p>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Invoices List -->
<div class="card">
    <div class="card-body">
        <h5 class="card-title mb-3">Riwayat Tagihan</h5>
        @if($customer->invoices->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No. Tagihan</th>
                            <th>Periode</th>
                            <th>Jatuh Tempo</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Dibayar Oleh</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->invoices as $invoice)
                            @php
                                $statusBadge = 'bg-secondary';
                                $statusText = $invoice->status;
                                $latestPayment = $invoice->payments->sortByDesc('created_at')->first();

                                switch($invoice->status) {
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
                            @endphp
                            <tr class="invoice-card">
                                <td>{{ $invoice->invoice_number }}</td>
                                <td>
                                    {{ \Carbon\Carbon::create()->month($invoice->month)->locale('id')->monthName }} {{ $invoice->year }}
                                </td>
                                <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</td>
                                <td>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                                <td>
                                    <span class="badge {{ $statusBadge }}">{{ $statusText }}</span>
                                </td>
                                <td>
                                    @if($invoice->status === 'PAID' && $latestPayment && $latestPayment->receivedBy)
                                        <div>
                                            <strong class="text-success">{{ $latestPayment->receivedBy->name }}</strong>
                                            @if($latestPayment->paid_date)
                                                <br><small class="text-muted">
                                                    <i class="ti ti-calendar me-1"></i>{{ $latestPayment->paid_date->format('d/m/Y') }}
                                                </small>
                                            @endif
                                            @if($latestPayment->created_at)
                                                <br><small class="text-muted">
                                                    <i class="ti ti-clock me-1"></i>{{ $latestPayment->created_at->format('H:i') }}
                                                </small>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($invoice->status !== 'PAID')
                                        <a href="{{ route('field-officer.invoices.payment', $invoice) }}" class="btn btn-sm btn-success">
                                            <i class="ti ti-check me-1"></i> Bayar
                                        </a>
                                    @else
                                        <span class="text-muted small">Sudah dibayar</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-4">
                <i class="ti ti-file-off fs-1 text-muted"></i>
                <p class="text-muted mt-2">Belum ada tagihan untuk pelanggan ini.</p>
            </div>
        @endif
    </div>
</div>

@push('scripts')
@if($customer->lat && $customer->lng)
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script>
    var map = L.map('customer-map').setView([{{ $customer->lat }}, {{ $customer->lng }}], 15);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    L.marker([{{ $customer->lat }}, {{ $customer->lng }}])
        .addTo(map)
        .bindPopup('{{ $customer->name }}')
        .openPopup();
</script>
@endif
@endpush
@endsection

