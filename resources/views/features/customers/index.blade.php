@extends('layouts.app')

@section('title', 'Manajemen Pelanggan')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    #customers-table_wrapper {
        overflow-x: auto;
    }
    #customers-table {
        width: 100% !important;
    }
    .table-responsive {
        position: relative;
    }
    @media (max-width: 768px) {
        .table-responsive {
            overflow-x: scroll;
            -webkit-overflow-scrolling: touch;
        }
    }
    #location-map {
        z-index: 1;
    }
    #search-results {
        border: 1px solid #ddd;
        border-radius: 4px;
        background: white;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    #search-results .list-group-item {
        cursor: pointer;
    }
    #search-results .list-group-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Manajemen Pelanggan"
    :breadcrumb-title="'Manajemen Pelanggan'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Daftar Pelanggan</h4>
                    <p class="card-subtitle mb-3">
                        Kelola data pelanggan, tambah, edit, dan hapus pelanggan dari halaman ini.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-create-customer">
                    <i class="ti ti-plus me-1"></i> Tambah Pelanggan Baru
                </button>
            </div>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="filter-type" class="form-label">Jenis Pelanggan</label>
                    <select id="filter-type" class="form-select">
                        <option value="">Semua</option>
                        <option value="rumahan">Rumahan</option>
                        <option value="kantor">Kantor</option>
                        <option value="sekolah">Sekolah</option>
                        <option value="free">Free</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-assigned" class="form-label">Penanggung Jawab</label>
                    <select id="filter-assigned" class="form-select">
                        <option value="">Semua</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-active" class="form-label">Status</label>
                    <select id="filter-active" class="form-select">
                        <option value="">Semua</option>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary w-100" id="btn-reset-filter">
                        <i class="ti ti-refresh me-1"></i> Reset Filter
                    </button>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table id="customers-table" class="table table-striped table-bordered align-middle" style="min-width: 1000px;">
                    <thead>
                        <tr>
                            <th style="min-width: 100px;">Kode</th>
                            <th style="min-width: 150px;">Nama</th>
                            <th style="min-width: 120px;">Telepon</th>
                            <th style="min-width: 100px;">Jenis</th>
                            <th style="min-width: 150px;">Penanggung Jawab</th>
                            <th style="min-width: 130px;">Biaya Bulanan</th>
                            <th style="min-width: 100px;">Status</th>
                            <th style="min-width: 120px;">Tanggal Dibuat</th>
                            <th style="min-width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Customer Modal -->
<x-ui.modal
    id="customerModal"
    title="Tambah Pelanggan Baru"
    size="xl"
    content-id="customerModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-submit-form">Simpan</button>
    </x-slot>
</x-ui.modal>

<!-- Device Management Modal -->
<x-ui.modal
    id="deviceModal"
    title="Kelola Perangkat Pelanggan"
    size="xl"
    content-id="deviceModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
    </x-slot>
</x-ui.modal>

@push('scripts')
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
$(document).ready(function() {
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#customers-table')) {
        $('#customers-table').DataTable().destroy();
    }

    // Initialize DataTable
    const customersTable = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('customers.index') }}",
            data: function(d) {
                var type = $('#filter-type').val();
                var assigned_to = $('#filter-assigned').val();
                var active = $('#filter-active').val();

                if (type) d.type = type;
                if (assigned_to) d.assigned_to = assigned_to;
                if (active) d.active = active;
            }
        },
        columns: [
            { data: 'customer_code', name: 'customer_code' },
            { data: 'name', name: 'name' },
            { data: 'phone', name: 'phone' },
            { data: 'type_badge', name: 'type', orderable: false, searchable: false },
            { data: 'assigned_user', name: 'assigned_user', orderable: false, searchable: false },
            { data: 'total_fee_formatted', name: 'total_fee' },
            { data: 'status_badge', name: 'active', orderable: false, searchable: false },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: false,
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        order: [[7, 'desc']],
        language: {
            processing: '<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>',
            lengthMenu: "Tampilkan _MENU_ entri",
            zeroRecords: "Tidak ada data yang ditemukan",
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

    // Apply filters
    $('#filter-type, #filter-assigned, #filter-active').on('change', function() {
        customersTable.ajax.reload();
    });

    // Reset filters
    $('#btn-reset-filter').on('click', function() {
        $('#filter-type, #filter-assigned, #filter-active').val('');
        customersTable.ajax.reload();
    });

    // Load create form
    $('#btn-create-customer').on('click', function() {
        Modal.load('customerModal', "{{ route('customers.create') }}", 'Tambah Pelanggan Baru');
        $('#btn-submit-form').show();
    });

    // Handle show button click
    $(document).on('click', '.btn-show-customer', function(e) {
        e.preventDefault();
        const customerId = $(this).data('customer-id');
        Modal.load('customerModal', `/customers/${customerId}`, 'Detail Pelanggan');
        $('#btn-submit-form').hide();
    });

    // Handle edit button click
    $(document).on('click', '.btn-edit-customer', function(e) {
        e.preventDefault();
        const customerId = $(this).data('customer-id');
        Modal.load('customerModal', `/customers/${customerId}/edit`, 'Edit Pelanggan');
        $('#btn-submit-form').show();
    });

    // Handle manage devices button click
    $(document).on('click', '.btn-manage-devices', function(e) {
        e.preventDefault();
        const customerId = $(this).data('customer-id');
        
        $.ajax({
            url: `/customers/${customerId}/devices`,
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                const html = response.html || response;
                $('#deviceModalBody').html(html);
                $('#deviceModal').modal('show');
                
                // Initialize device management after modal is shown
                setTimeout(function() {
                    initDeviceManagement(customerId);
                }, 300);
            },
            error: function() {
                Toast.error('Gagal memuat halaman kelola perangkat.');
            }
        });
    });

    // Handle form submission
    $('#btn-submit-form').on('click', function() {
        Form.submit('#customer-form', {
            success: function(response) {
                if (response.success) {
                    Modal.hide('customerModal');
                    Toast.success(response.message);
                    customersTable.ajax.reload(null, false);
                }
            }
        });
    });

    // Handle delete button click
    $(document).on('click', '.btn-delete-customer', function(e) {
        e.preventDefault();
        const customerId = $(this).data('customer-id');
        const customerName = $(this).data('customer-name');

        if (!confirm(`Apakah Anda yakin ingin menghapus pelanggan "${customerName}"?`)) {
            return;
        }

        $.ajax({
            url: `/customers/${customerId}`,
            method: 'POST',
            data: {
                _method: 'DELETE',
                _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
            },
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    customersTable.ajax.reload(null, false);
                }
            },
            error: function() {
                Toast.error('Gagal menghapus pelanggan.');
            }
        });
    });

    // Reset form when modal is hidden
    $('#customerModal').on('hidden.bs.modal', function() {
        Modal.clear('customerModal');
        $('#btn-submit-form').show();

        // Clean up map
        if (window.locationMap) {
            window.locationMap.remove();
            window.locationMap = null;
            window.locationMarker = null;
        }

        // Reset search
        $('#location-search').val('');
        $('#search-results').hide().empty();
    });

    // Initialize map for customer form
    let locationMap;
    let locationMarker;

    function updateMarkerPosition(lat, lng) {
        if (!locationMap) return;

        if (locationMarker) {
            locationMarker.setLatLng([lat, lng]);
        } else {
            locationMarker = L.marker([lat, lng], { draggable: true })
                .addTo(locationMap);

            locationMarker.on('dragend', function(e) {
                const position = locationMarker.getLatLng();
                $('#lat').val(position.lat.toFixed(7));
                $('#lng').val(position.lng.toFixed(7));
            });
        }

        locationMap.setView([lat, lng], 15);
        $('#lat').val(lat.toFixed(7));
        $('#lng').val(lng.toFixed(7));
    }

    function searchLocation(query) {
        if (!query || query.trim() === '') {
            $('#search-results').hide().empty();
            return;
        }

        // Show loading
        $('#search-results').html('<div class="list-group-item text-center"><small class="text-muted">Mencari...</small></div>').show();

        // Use Nominatim API (free, no API key needed)
        const url = `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&limit=5&countrycodes=id`;

        $.ajax({
            url: url,
            method: 'GET',
            headers: {
                'User-Agent': 'TagihanApp/1.0'
            },
            success: function(data) {
                const resultsDiv = $('#search-results');
                resultsDiv.empty();

                if (data.length === 0) {
                    resultsDiv.html('<div class="list-group-item text-center"><small class="text-muted">Tidak ada hasil ditemukan</small></div>');
                    return;
                }

                data.forEach(function(item) {
                    const displayName = item.display_name.length > 80
                        ? item.display_name.substring(0, 80) + '...'
                        : item.display_name;

                    const listItem = $('<a href="#" class="list-group-item list-group-item-action"></a>')
                        .html(`<div><strong>${displayName}</strong></div><small class="text-muted">Lat: ${parseFloat(item.lat).toFixed(6)}, Lng: ${parseFloat(item.lon).toFixed(6)}</small>`)
                        .on('click', function(e) {
                            e.preventDefault();
                            const lat = parseFloat(item.lat);
                            const lng = parseFloat(item.lon);
                            updateMarkerPosition(lat, lng);
                            $('#location-search').val(item.display_name);
                            resultsDiv.hide();
                        });

                    resultsDiv.append(listItem);
                });
            },
            error: function() {
                $('#search-results').html('<div class="list-group-item text-center"><small class="text-danger">Gagal mencari lokasi</small></div>');
            }
        });
    }

    function initLocationMap() {
        // Check if map container exists
        if (!$('#location-map').length) {
            return;
        }

        // Remove existing map if any
        if (locationMap) {
            locationMap.remove();
        }

        // Get initial coordinates
        const initialLat = parseFloat($('#lat').val()) || -6.2088;
        const initialLng = parseFloat($('#lng').val()) || 106.8456;

        // Initialize map
        locationMap = L.map('location-map').setView([initialLat, initialLng], 13);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors',
            maxZoom: 19
        }).addTo(locationMap);

        // Add marker
        if ($('#lat').val() && $('#lng').val()) {
            locationMarker = L.marker([initialLat, initialLng], { draggable: true })
                .addTo(locationMap);
        } else {
            locationMarker = L.marker([initialLat, initialLng], { draggable: true })
                .addTo(locationMap);
            $('#lat').val(initialLat.toFixed(7));
            $('#lng').val(initialLng.toFixed(7));
        }

        // Update inputs when marker is dragged
        locationMarker.on('dragend', function(e) {
            const position = locationMarker.getLatLng();
            $('#lat').val(position.lat.toFixed(7));
            $('#lng').val(position.lng.toFixed(7));
        });

        // Update marker when map is clicked
        locationMap.on('click', function(e) {
            const lat = e.latlng.lat;
            const lng = e.latlng.lng;
            updateMarkerPosition(lat, lng);
        });

        // Invalidate size after a short delay to ensure modal is fully rendered
        setTimeout(function() {
            locationMap.invalidateSize();
        }, 100);

        // Update marker when lat/lng inputs change manually
        $('#lat, #lng').off('input.locationMap').on('input.locationMap', function() {
            if (locationMap && locationMarker) {
                const lat = parseFloat($('#lat').val());
                const lng = parseFloat($('#lng').val());
                if (lat && lng && !isNaN(lat) && !isNaN(lng)) {
                    locationMarker.setLatLng([lat, lng]);
                    locationMap.setView([lat, lng], locationMap.getZoom());
                }
            }
        });

        // Search location functionality
        $('#btn-search-location').off('click.locationSearch').on('click.locationSearch', function() {
            const query = $('#location-search').val();
            searchLocation(query);
        });

        $('#location-search').off('keypress.locationSearch').on('keypress.locationSearch', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const query = $(this).val();
                searchLocation(query);
            }
        });

        // Hide results when clicking outside
        $(document).off('click.locationSearch').on('click.locationSearch', function(e) {
            if (!$(e.target).closest('#location-search, #btn-search-location, #search-results').length) {
                $('#search-results').hide();
            }
        });
    }

    // Initialize map when customer modal is shown
    $('#customerModal').on('shown.bs.modal', function() {
        setTimeout(function() {
            initLocationMap();
        }, 500);
    });

    // Device Management Functions
    function initDeviceManagement(customerId) {
        if (!customerId) {
            console.error('Customer ID not found');
            return;
        }

        // Function to reload device list
        function reloadDeviceList() {
            $.ajax({
                url: `/customers/${customerId}/devices`,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    const html = response.html || response;
                    const tempDiv = $('<div>').html(html);
                    const newDeviceList = tempDiv.find('#devices-list').html();
                    if (newDeviceList) {
                        $('#devices-list').html(newDeviceList);
                        // Re-initialize event handlers for new buttons
                        initDeviceManagement(customerId);
                    }
                },
                error: function(xhr) {
                    console.error('Failed to reload device list', xhr);
                    Toast.error('Gagal memuat ulang daftar perangkat.');
                }
            });
        }

        // Reset device form
        function resetDeviceForm() {
            const form = document.getElementById('device-form');
            if (form) {
                form.reset();
                $('#device_id').val('');
                $('#device-photo-preview').hide();
                $('#location-photo-preview').hide();
            }
        }

        // Reset device form
        $('#deviceModal').off('click', '#btn-reset-device-form').on('click', '#btn-reset-device-form', function(e) {
            e.preventDefault();
            e.stopPropagation();
            resetDeviceForm();
        });

        // Preview device photo
        $('#deviceModal').off('change', '#device-photo').on('change', '#device-photo', function(e) {
            e.stopPropagation();
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#device-photo-img').attr('src', e.target.result);
                    $('#device-photo-preview').show();
                };
                reader.readAsDataURL(file);
            }
        });

        // Preview location photo
        $('#deviceModal').off('change', '#location-photo').on('change', '#location-photo', function(e) {
            e.stopPropagation();
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#location-photo-img').attr('src', e.target.result);
                    $('#location-photo-preview').show();
                };
                reader.readAsDataURL(file);
            }
        });

        // Submit device form
        $('#deviceModal').off('click', '#btn-submit-device-form').on('click', '#btn-submit-device-form', function(e) {
            e.preventDefault();
            e.stopPropagation();

            const form = document.getElementById('device-form');
            if (!form) {
                console.error('Device form not found');
                Toast.error('Form perangkat tidak ditemukan.');
                return;
            }

            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            const formData = new FormData(form);
            const deviceId = $('#device_id').val();
            const isEdit = deviceId !== '';
            const url = isEdit
                ? `/customers/${customerId}/devices/${deviceId}`
                : `/customers/${customerId}/devices`;

            formData.append('_method', isEdit ? 'PUT' : 'POST');

            const submitBtn = $(this);
            const originalText = submitBtn.html();
            submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

            $.ajax({
                url: url,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        Toast.success(response.message);
                        resetDeviceForm();
                        reloadDeviceList();
                    } else {
                        Toast.error(response.message || 'Gagal menyimpan perangkat.');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    if (response && response.errors) {
                        let errorMsg = 'Validasi gagal:\n';
                        Object.keys(response.errors).forEach(key => {
                            errorMsg += response.errors[key][0] + '\n';
                        });
                        Toast.error(errorMsg);
                    } else {
                        Toast.error(response?.message || 'Gagal menyimpan perangkat.');
                    }
                },
                complete: function() {
                    submitBtn.prop('disabled', false).html(originalText);
                }
            });
        });

        // Edit device
        $('#deviceModal').off('click', '.btn-edit-device').on('click', '.btn-edit-device', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const deviceId = $(this).data('device-id');
            
            if (!deviceId) {
                Toast.error('ID perangkat tidak ditemukan.');
                return;
            }

            $.ajax({
                url: `/customers/${customerId}/devices/${deviceId}`,
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(device) {
                    $('#device_id').val(device.id);
                    $('#device-name').val(device.name || '');
                    $('#device-mac').val(device.mac_address || '');
                    $('#device-note').val(device.note || '');

                    if (device.device_photo_url) {
                        $('#device-photo-img').attr('src', device.device_photo_url);
                        $('#device-photo-preview').show();
                    } else {
                        $('#device-photo-preview').hide();
                    }

                    if (device.location_photo_url) {
                        $('#location-photo-img').attr('src', device.location_photo_url);
                        $('#location-photo-preview').show();
                    } else {
                        $('#location-photo-preview').hide();
                    }

                    $('html, body').animate({
                        scrollTop: $('#device-form-container').offset().top - 100
                    }, 500);
                },
                error: function() {
                    Toast.error('Gagal memuat data perangkat.');
                }
            });
        });

        // Delete device
        $('#deviceModal').off('click', '.btn-delete-device').on('click', '.btn-delete-device', function(e) {
            e.preventDefault();
            e.stopPropagation();
            const deviceId = $(this).data('device-id');
            
            if (!deviceId) {
                Toast.error('ID perangkat tidak ditemukan.');
                return;
            }

            if (!confirm('Apakah Anda yakin ingin menghapus perangkat ini?')) {
                return false;
            }

            $.ajax({
                url: `/customers/${customerId}/devices/${deviceId}`,
                method: 'POST',
                data: {
                    _method: 'DELETE',
                    _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                },
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        Toast.success(response.message);
                        reloadDeviceList();
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    Toast.error(response?.message || 'Gagal menghapus perangkat.');
                }
            });
        });
    }
});
</script>
@endpush
@endsection

