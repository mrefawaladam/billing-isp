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
    .map-filters {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 1000;
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        min-width: 250px;
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
    .routing-control {
        position: absolute;
        top: 10px;
        left: 10px;
        z-index: 1000;
        background: white;
        padding: 15px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        min-width: 280px;
    }
    .routing-control.active-mode {
        border: 2px solid #0d6efd;
        background: #f0f7ff;
    }
    .routing-control h6 {
        margin-bottom: 10px;
    }
    .routing-control .btn-group {
        width: 100%;
        margin-bottom: 10px;
    }
    .routing-control .btn-group .btn {
        flex: 1;
    }
    .routing-control .route-info {
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #ddd;
        font-size: 12px;
    }
    .routing-control .route-info strong {
        display: block;
        margin-bottom: 5px;
    }
    .routing-control .route-info .route-detail {
        color: #666;
        margin-bottom: 3px;
    }
    .leaflet-routing-container {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
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

    <!-- Routing Control -->
    <div class="routing-control">
        <h6>Rute & Tracing</h6>
        <div class="btn-group mb-2" role="group">
            <button type="button" class="btn btn-sm btn-primary" id="btn-start-route">
                <i class="ti ti-route me-1"></i> Mulai Rute
            </button>
            <button type="button" class="btn btn-sm btn-secondary" id="btn-clear-route">
                <i class="ti ti-x me-1"></i> Hapus
            </button>
        </div>
        <div class="mb-2">
            <small class="text-muted" id="routing-instruction">Klik "Mulai Rute" untuk mengaktifkan mode routing</small>
        </div>
        <div id="route-info" class="route-info" style="display: none;">
            <strong>Informasi Rute:</strong>
            <div class="route-detail" id="route-distance">Jarak: -</div>
            <div class="route-detail" id="route-time">Waktu: -</div>
            <div class="route-detail" id="route-waypoints">Titik: 0</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="map-filters">
        <h6 class="mb-3">Filter</h6>
        <div class="mb-3">
            <label for="filter-assigned" class="form-label small">Penanggung Jawab</label>
            <select id="filter-assigned" class="form-select form-select-sm">
                <option value="">Semua</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label for="filter-type" class="form-label small">Jenis Pelanggan</label>
            <select id="filter-type" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="rumahan">Rumahan</option>
                <option value="kantor">Kantor</option>
                <option value="sekolah">Sekolah</option>
                <option value="free">Free</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="filter-invoice-status" class="form-label small">Status Tagihan</label>
            <select id="filter-invoice-status" class="form-select form-select-sm">
                <option value="">Semua</option>
                <option value="PAID">Sudah Dibayar</option>
                <option value="DUE">Jatuh Tempo</option>
                <option value="UNPAID">Belum Dibayar / Telat</option>
            </select>
        </div>
        <button type="button" class="btn btn-sm btn-secondary w-100" id="btn-reset-filter">
            <i class="ti ti-refresh me-1"></i> Reset
        </button>
    </div>

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
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBq64_rUcNjJa0sDVuc12mqg0rhr6zAyV0&libraries=geometry,directions"></script>
<script>
let map;
let markers = [];
let currentInvoiceId = null;
let routePolyline = null;
let routeMarkers = [];
let isRoutingMode = false;
let routeWaypoints = [];
let directionsService = null;

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
function createPopupContent(customer, showRouteButton) {
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
        ${showRouteButton && isRoutingMode ? `
            <div class="mt-2">
                <button class="btn btn-sm btn-primary w-100 btn-add-to-route"
                        data-lat="${customer.lat}"
                        data-lng="${customer.lng}"
                        data-name="${customer.name}">
                    <i class="ti ti-plus me-1"></i> Tambah ke Rute
                </button>
            </div>
        ` : ''}
        ${customer.latest_invoice && customer.latest_invoice.status !== 'PAID' ? `
            <div class="mt-3">
                <button class="btn btn-sm btn-success w-100 btn-mark-paid-map"
                        data-invoice-id="${customer.latest_invoice.id}">
                    <i class="ti ti-check me-1"></i> Sudah Dibayar
                </button>
            </div>
        ` : ''}
    </div>
`;
}

function loadCustomers() {
    // Clear existing markers
    markers.forEach(marker => map.removeLayer(marker));
    markers = [];

    const assignedTo = $('#filter-assigned').val();
    const type = $('#filter-type').val();
    const invoiceStatus = $('#filter-invoice-status').val();

    $.ajax({
        url: "{{ route('map.customers') }}",
        method: 'GET',
        data: {
            assigned_to: assignedTo,
            type: type,
            invoice_status: invoiceStatus
        },
        success: function(response) {
            if (response.success && response.data) {
                response.data.forEach(function(customer) {
                    const icon = createCustomIcon(iconColors[customer.marker_color] || iconColors.gray);

                    const marker = L.marker([customer.lat, customer.lng], {
                        icon: icon,
                        customerData: customer
                    })
                        .addTo(map)
                        .bindPopup(createPopupContent(customer, true));

                    // Store customer data in marker for later use
                    marker._customerData = customer;

                    // Add click handler for routing - when routing mode is active, clicking marker adds to route
                    marker.on('click', function() {
                        if (isRoutingMode) {
                            // Small delay to allow popup to open first, then add to route
                            setTimeout(function() {
                                // Check if this marker is already in route
                                const alreadyInRoute = routeWaypoints.some(wp =>
                                    Math.abs(wp.lat - customer.lat) < 0.0001 &&
                                    Math.abs(wp.lng - customer.lng) < 0.0001
                                );

                                if (!alreadyInRoute) {
                                    addWaypoint(customer.lat, customer.lng, customer.name);
                                } else {
                                    Toast.info('Pelanggan ini sudah ada di rute');
                                }
                            }, 200);
                        }
                    });

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

    // Initialize Google Directions Service
    if (typeof google !== 'undefined' && google.maps) {
        directionsService = new google.maps.DirectionsService();
    }

    // Load customers
    loadCustomers();

    // Handle filter changes
    $('#filter-assigned, #filter-type, #filter-invoice-status').on('change', function() {
        // Clear route when filters change
        if (routingControl) {
            clearRoute();
        }
        loadCustomers();
    });

    // Reset filters
    $('#btn-reset-filter').on('click', function() {
        $('#filter-assigned, #filter-type, #filter-invoice-status').val('');
        loadCustomers();
    });


    // Handle confirm payment button click
    $('#btn-confirm-payment').on('click', function() {
        if (!currentInvoiceId) {
            Toast.error('Invoice ID tidak ditemukan.');
            return;
        }

        const btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');

        $.ajax({
            url: `/invoices/${currentInvoiceId}`,
            method: 'POST',
            data: {
                _method: 'PUT',
                status: 'PAID',
                _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    $('#confirmPaymentModal').modal('hide');
                    Toast.success(response.message);
                    loadCustomers(); // Reload map markers
                } else {
                    Toast.error(response.message || 'Gagal memperbarui status tagihan.');
                }
            },
            error: function(xhr) {
                const response = xhr.responseJSON;
                Toast.error(response?.message || 'Gagal memperbarui status tagihan.');
            },
            complete: function() {
                btn.prop('disabled', false).html('Konfirmasi Pembayaran');
                currentInvoiceId = null;
            }
        });
    });

    // Reset when modal is hidden
    $('#confirmPaymentModal').on('hidden.bs.modal', function() {
        currentInvoiceId = null;
    });

    // Routing functionality
    function addWaypoint(lat, lng, name) {
        routeWaypoints.push({
            lat: lat,
            lng: lng,
            name: name
        });

        updateRoute();
        Toast.success(`Titik "${name}" ditambahkan ke rute`);
    }

    function updateRoute() {
        if (routeWaypoints.length < 2) {
            clearRouteDisplay();
            $('#route-info').hide();
            return;
        }

        // Clear existing route
        clearRouteDisplay();

        // Validate waypoints
        const validWaypoints = routeWaypoints
            .filter(wp => wp.lat && wp.lng && !isNaN(wp.lat) && !isNaN(wp.lng))
            .map(wp => ({
                lat: parseFloat(wp.lat),
                lng: parseFloat(wp.lng),
                name: wp.name
            }));

        if (validWaypoints.length < 2) {
            Toast.error('Koordinat tidak valid. Pastikan semua titik memiliki koordinat yang benar.');
            return;
        }

        // Show loading state
        $('#route-info').show();
        $('#route-distance').text('Jarak: Menghitung...');
        $('#route-time').text('Waktu: Menghitung...');
        $('#route-waypoints').text(`Titik: ${validWaypoints.length}`);

        // Use Google Directions API
        if (directionsService && typeof google !== 'undefined') {
            calculateGoogleRoute(validWaypoints);
        } else {
            // Fallback to simple calculation
            calculateSimpleRoute(validWaypoints);
        }
    }

    function clearRouteDisplay() {
        // Remove route polyline
        if (routePolyline) {
            map.removeLayer(routePolyline);
            routePolyline = null;
        }

        // Remove route markers
        routeMarkers.forEach(marker => map.removeLayer(marker));
        routeMarkers = [];

        // Remove simple route lines if any
        if (map._simpleRouteLines) {
            map._simpleRouteLines.forEach(line => map.removeLayer(line));
            map._simpleRouteLines = [];
        }
    }

    function calculateGoogleRoute(waypoints) {
        if (!directionsService) {
            calculateSimpleRoute(waypoints);
            return;
        }

        // Prepare waypoints for Google Directions API
        const origin = new google.maps.LatLng(waypoints[0].lat, waypoints[0].lng);
        const destination = new google.maps.LatLng(waypoints[waypoints.length - 1].lat, waypoints[waypoints.length - 1].lng);

        // Intermediate waypoints (skip first and last)
        const intermediateWaypoints = waypoints.slice(1, -1).map(wp => ({
            location: new google.maps.LatLng(wp.lat, wp.lng),
            stopover: true
        }));

        const request = {
            origin: origin,
            destination: destination,
            waypoints: intermediateWaypoints.length > 0 ? intermediateWaypoints : undefined,
            travelMode: google.maps.TravelMode.DRIVING,
            optimizeWaypoints: false
        };

        directionsService.route(request, function(result, status) {
            if (status === google.maps.DirectionsStatus.OK) {
                // Draw route on Leaflet map
                const route = result.routes[0];
                const path = route.overview_path;

                // Convert Google LatLng to Leaflet LatLng array
                const leafletPath = path.map(point => [point.lat(), point.lng()]);

                // Draw polyline
                routePolyline = L.polyline(leafletPath, {
                    color: '#3388ff',
                    opacity: 0.8,
                    weight: 5
                }).addTo(map);

                // Add waypoint markers
                waypoints.forEach(function(wp, index) {
                    const markerColor = index === 0 ? '#28a745' : index === waypoints.length - 1 ? '#dc3545' : '#ffc107';
                    const marker = L.marker([wp.lat, wp.lng], {
                        icon: L.divIcon({
                            className: 'route-waypoint-marker',
                            html: `<div style="
                                background-color: ${markerColor};
                                width: 25px;
                                height: 25px;
                                border-radius: 50%;
                                border: 3px solid white;
                                box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                color: white;
                                font-weight: bold;
                                font-size: 12px;
                            ">${index + 1}</div>`,
                            iconSize: [25, 25],
                            iconAnchor: [12, 25]
                        })
                    }).addTo(map);
                    routeMarkers.push(marker);
                });

                // Calculate total distance and time
                let totalDistance = 0;
                let totalTime = 0;

                route.legs.forEach(function(leg) {
                    totalDistance += leg.distance.value; // in meters
                    totalTime += leg.duration.value; // in seconds
                });

                const distanceKm = (totalDistance / 1000).toFixed(2);
                const timeMinutes = Math.round(totalTime / 60);

                $('#route-distance').text(`Jarak: ${distanceKm} km`);
                $('#route-time').text(`Waktu: ${timeMinutes} menit`);
                $('#route-waypoints').text(`Titik: ${waypoints.length}`);

                // Fit map to show route
                if (routePolyline) {
                    map.fitBounds(routePolyline.getBounds().pad(0.1));
                }
            } else {
                console.error('Google Directions error:', status);
                Toast.warning('Gagal menghitung rute dengan Google Maps. Menggunakan perhitungan perkiraan.');
                calculateSimpleRoute(waypoints);
            }
        });
    }

    // Fallback function to calculate simple route (straight line distance)
    function calculateSimpleRoute(waypoints) {
        if (waypoints.length < 2) return;

        let totalDistance = 0;
        const leafletWaypoints = [];

        for (let i = 0; i < waypoints.length; i++) {
            const wp = L.latLng(waypoints[i].lat, waypoints[i].lng);
            leafletWaypoints.push(wp);

            if (i > 0) {
                const from = leafletWaypoints[i - 1];
                const to = wp;
                const distance = from.distanceTo(to); // Distance in meters
                totalDistance += distance;
            }
        }

        // Draw simple polyline between waypoints
        routePolyline = L.polyline(
            waypoints.map(wp => [wp.lat, wp.lng]),
            {
                color: '#3388ff',
                opacity: 0.8,
                weight: 5,
                dashArray: '10, 5' // Dashed line to indicate it's approximate
            }
        ).addTo(map);

        // Add waypoint markers
        waypoints.forEach(function(wp, index) {
            const markerColor = index === 0 ? '#28a745' : index === waypoints.length - 1 ? '#dc3545' : '#ffc107';
            const marker = L.marker([wp.lat, wp.lng], {
                icon: L.divIcon({
                    className: 'route-waypoint-marker',
                    html: `<div style="
                        background-color: ${markerColor};
                        width: 25px;
                        height: 25px;
                        border-radius: 50%;
                        border: 3px solid white;
                        box-shadow: 0 2px 5px rgba(0,0,0,0.3);
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        color: white;
                        font-weight: bold;
                        font-size: 12px;
                    ">${index + 1}</div>`,
                    iconSize: [25, 25],
                    iconAnchor: [12, 25]
                })
            }).addTo(map);
            routeMarkers.push(marker);
        });

        // Calculate estimated time (assuming average speed of 40 km/h in city)
        const distanceKm = (totalDistance / 1000).toFixed(2);
        const estimatedTime = Math.round((totalDistance / 1000) / 40 * 60); // minutes

        $('#route-distance').text(`Jarak: ~${distanceKm} km (perkiraan)`);
        $('#route-time').text(`Waktu: ~${estimatedTime} menit (perkiraan)`);
        $('#route-waypoints').text(`Titik: ${waypoints.length}`);
    }

    function clearRoute() {
        clearRouteDisplay();
        routeWaypoints = [];
        $('#route-info').hide();
        isRoutingMode = false;
        $('#btn-start-route').removeClass('active').html('<i class="ti ti-route me-1"></i> Mulai Rute');
        $('.routing-control').removeClass('active-mode');
        $('#routing-instruction').html('Klik "Mulai Rute" untuk mengaktifkan mode routing');
        updateMarkersForRouting();
        Toast.info('Rute dihapus');
    }

    // Start routing mode
    $('#btn-start-route').on('click', function() {
        if (isRoutingMode) {
            // If already in routing mode, add current location as starting point
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(position) {
                    addWaypoint(position.coords.latitude, position.coords.longitude, 'Lokasi Saya');
                }, function() {
                    Toast.warning('Tidak dapat mengakses lokasi Anda. Klik marker pelanggan untuk memulai rute.');
                });
            } else {
                Toast.info('Klik marker pelanggan untuk menambahkan titik rute');
            }
        } else {
            isRoutingMode = true;
            $(this).addClass('active').html('<i class="ti ti-route me-1"></i> Mode Rute Aktif');
            $('.routing-control').addClass('active-mode');
            $('#routing-instruction').html('<strong class="text-primary">Mode Aktif:</strong> Klik marker pelanggan untuk menambahkan ke rute');
            updateMarkersForRouting();
            Toast.success('Mode rute aktif! Klik marker pelanggan untuk menambahkan ke rute.');
        }
    });

    // Clear route
    $('#btn-clear-route').on('click', function() {
        clearRoute();
    });

    // Handle add to route button in popup (delegated event for dynamically added content)
    $(document).on('click', '.btn-add-to-route', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const lat = parseFloat($(this).data('lat'));
        const lng = parseFloat($(this).data('lng'));
        const name = $(this).data('name');
        addWaypoint(lat, lng, name);
    });

    // Handle mark as paid button in popup (delegated event for dynamically added content)
    $(document).on('click', '.btn-mark-paid-map', function(e) {
        e.preventDefault();
        e.stopPropagation();
        const invoiceId = $(this).data('invoice-id');
        currentInvoiceId = invoiceId;

        // Load invoice detail untuk konfirmasi
        $.ajax({
            url: `/invoices/${invoiceId}?confirm=payment`,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.html) {
                    $('#confirmPaymentModalBody').html(response.html);
                    $('#confirmPaymentModal').modal('show');
                }
            },
            error: function() {
                Toast.error('Gagal memuat detail tagihan.');
            }
        });
    });

    // Update popup content when routing mode changes
    function updateMarkersForRouting() {
        markers.forEach(function(marker) {
            if (marker._customerData) {
                const customer = marker._customerData;
                const newContent = createPopupContent(customer, true);
                marker.setPopupContent(newContent);
            }
        });
    }

});
</script>
@endpush
@endsection

