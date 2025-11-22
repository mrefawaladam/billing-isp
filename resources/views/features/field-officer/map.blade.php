@extends('layouts.app')

@section('title', 'Peta Lokasi Pelanggan')

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #map {
        height: calc(100vh - 200px);
        width: 100%;
        z-index: 1;
    }
    .map-container {
        position: relative;
    }
    .legend {
        position: absolute;
        bottom: 30px;
        right: 10px;
        z-index: 1000;
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .legend-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
    }
    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 50%;
        margin-right: 10px;
        border: 2px solid #333;
    }
    .popup-content {
        min-width: 250px;
    }
    .popup-content h6 {
        margin-bottom: 10px;
        color: #333;
    }
    .popup-content .info-row {
        margin-bottom: 8px;
        font-size: 13px;
    }
    .popup-content .info-label {
        font-weight: bold;
        color: #666;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Peta Lokasi Pelanggan"
    :breadcrumb-title="'Peta Lokasi Pelanggan'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<div class="map-container">
    <div id="map"></div>

    <!-- Legend -->
    <div class="legend">
        <h6 class="mb-3">Legenda</h6>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #28a745;"></div>
            <span>Sudah Dibayar</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #ffc107;"></div>
            <span>Jatuh Tempo</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #dc3545;"></div>
            <span>Belum Dibayar / Telat</span>
        </div>
        <div class="legend-item">
            <div class="legend-color" style="background-color: #6c757d;"></div>
            <span>Belum Ada Tagihan</span>
        </div>
    </div>
</div>

<!-- Confirm Payment Modal -->
<x-ui.modal
    id="confirmPaymentModal"
    title="Konfirmasi Pembayaran"
    size="lg"
    content-id="confirmPaymentModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-success" id="btn-confirm-payment">Konfirmasi Pembayaran</button>
    </x-slot>
</x-ui.modal>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
let map;
let markers = [];
let currentInvoiceId = null;

// Custom icon colors
const iconColors = {
    green: '#28a745',
    yellow: '#ffc107',
    red: '#dc3545',
    gray: '#6c757d'
};

function createCustomIcon(color) {
    return L.divIcon({
        className: 'custom-marker',
        html: `<div style="
            background-color: ${color};
            width: 30px;
            height: 30px;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            border: 3px solid white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        "></div>`,
        iconSize: [30, 30],
        iconAnchor: [15, 30],
        popupAnchor: [0, -30]
    });
}

// Helper function to create popup content
function createPopupContent(customer) {
    return `
    <div class="popup-content">
        <h6>${customer.name}</h6>
        <div class="info-row">
            <span class="info-label">Kode:</span> ${customer.code || '-'}
        </div>
        <div class="info-row">
            <span class="info-label">Alamat:</span> ${customer.address || '-'}
        </div>
        <div class="info-row">
            <span class="info-label">Telepon:</span> ${customer.phone || '-'}
        </div>
        <div class="info-row">
            <span class="info-label">Paket:</span> Rp ${new Intl.NumberFormat('id-ID').format(customer.total_fee || 0)}
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="badge ${customer.marker_color === 'green' ? 'bg-success' : customer.marker_color === 'yellow' ? 'bg-warning' : 'bg-danger'}">
                ${customer.invoice_status_text}
            </span>
        </div>
        ${customer.latest_invoice ? `
            <div class="info-row">
                <span class="info-label">Tagihan:</span> ${customer.latest_invoice.invoice_number || '-'}
            </div>
            <div class="info-row">
                <span class="info-label">Jatuh Tempo:</span> ${customer.latest_invoice.due_date || '-'}
            </div>
            <div class="info-row">
                <span class="info-label">Total:</span> Rp ${new Intl.NumberFormat('id-ID').format(customer.latest_invoice.total_amount || 0)}
            </div>
        ` : ''}
        ${customer.latest_invoice && customer.latest_invoice.status !== 'PAID' ? `
            <div class="mt-3">
                <a href="/field-officer/invoices/${customer.latest_invoice.id}/payment" class="btn btn-sm btn-success w-100">
                    <i class="ti ti-check me-1"></i> Bayar Sekarang
                </a>
            </div>
        ` : ''}
        <div class="mt-2">
            <a href="/field-officer/customers/${customer.id}" class="btn btn-sm btn-primary w-100">
                <i class="ti ti-eye me-1"></i> Lihat Detail
            </a>
        </div>
    </div>
    `;
}

function loadCustomers() {
    // Clear existing markers
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];

    $.ajax({
        url: "{{ route('field-officer.map.customers') }}",
        method: 'GET',
        success: function(response) {
            if (response.success && response.data) {
                response.data.forEach(function(customer) {
                    const icon = createCustomIcon(iconColors[customer.marker_color] || iconColors.gray);

                    const marker = L.marker([customer.lat, customer.lng], {
                        icon: icon,
                        customerData: customer
                    })
                        .addTo(map)
                        .bindPopup(createPopupContent(customer));

                    markers.push(marker);
                });

                // Fit map to show all markers
                if (markers.length > 0) {
                    const group = new L.featureGroup(markers);
                    map.fitBounds(group.getBounds().pad(0.1));
                }
            }
        },
        error: function() {
            Toast.error('Gagal memuat data pelanggan.');
        }
    });
}

$(document).ready(function() {
    // Initialize map (default to Indonesia center)
    map = L.map('map').setView([-6.2088, 106.8456], 10);

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors',
        maxZoom: 19
    }).addTo(map);

    // Load customers
    loadCustomers();

    // Try to get user's current location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            map.setView([position.coords.latitude, position.coords.longitude], 13);
        });
    }
});
</script>
@endpush
@endsection

