@extends('layouts.app')

@section('title', 'Manajemen Inventory')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<style>
    #inventory-table_wrapper {
        overflow-x: auto;
    }
    #inventory-table {
        width: 100% !important;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Manajemen Inventory"
    :breadcrumb-title="'Manajemen Inventory'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- Low Stock Alerts -->
@if($lowStockItems->count() > 0 || $outOfStockItems->count() > 0)
<div class="row mb-4">
    @if($outOfStockItems->count() > 0)
    <div class="col-12 mb-3">
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="ti ti-alert-circle me-2 fs-4"></i>
            <div>
                <strong>Peringatan!</strong> Ada <strong>{{ $outOfStockItems->count() }}</strong> item yang stoknya habis:
                @foreach($outOfStockItems as $item)
                    <strong>{{ $item->name }}</strong>@if(!$loop->last), @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    @if($lowStockItems->count() > 0)
    <div class="col-12">
        <div class="alert alert-warning d-flex align-items-center" role="alert">
            <i class="ti ti-alert-triangle me-2 fs-4"></i>
            <div>
                <strong>Perhatian!</strong> Ada <strong>{{ $lowStockItems->count() }}</strong> item yang stoknya menipis:
                @foreach($lowStockItems as $item)
                    <strong>{{ $item->name }}</strong> ({{ $item->stock_quantity }} {{ $item->unit }})@if(!$loop->last), @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endif

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Daftar Inventory</h4>
                    <p class="card-subtitle mb-3">
                        Kelola stok perangkat, tambah, edit, dan gunakan item dari halaman ini.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-create-inventory">
                    <i class="ti ti-plus me-1"></i> Tambah Item Baru
                </button>
            </div>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="filter-type" class="form-label">Jenis Item</label>
                    <select id="filter-type" class="form-select">
                        <option value="">Semua</option>
                        <option value="router">Router</option>
                        <option value="ont">ONT</option>
                        <option value="kabel">Kabel</option>
                        <option value="connector">Connector</option>
                        <option value="switch">Switch</option>
                        <option value="access_point">Access Point</option>
                        <option value="lainnya">Lainnya</option>
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
                <div class="col-md-3">
                    <label for="filter-low-stock" class="form-label">Stok</label>
                    <select id="filter-low-stock" class="form-select">
                        <option value="">Semua</option>
                        <option value="1">Stok Menipis</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary w-100" id="btn-reset-filter">
                        <i class="ti ti-refresh me-1"></i> Reset Filter
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="inventory-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Kode</th>
                            <th>Nama</th>
                            <th>Jenis</th>
                            <th>Brand/Model</th>
                            <th>Stok</th>
                            <th>Harga</th>
                            <th>Status Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal -->
<x-ui.modal
    id="inventoryModal"
    title="Tambah Item Inventory Baru"
    size="lg"
    content-id="inventoryModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-submit-form">Simpan</button>
    </x-slot>
</x-ui.modal>

@push('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(document).ready(function() {
    let table = $('#inventory-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('inventory.index') }}",
            data: function(d) {
                d.type = $('#filter-type').val();
                d.active = $('#filter-active').val();
                d.low_stock = $('#filter-low-stock').val();
            }
        },
        columns: [
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'type', name: 'type' },
            { data: 'brand', name: 'brand', render: function(data, type, row) {
                return (row.brand || '') + (row.model ? ' / ' + row.model : '');
            }},
            { data: 'stock_info', name: 'stock_info', orderable: false },
            { data: 'price_formatted', name: 'price', orderable: false },
            { data: 'stock_status', name: 'stock_status', orderable: false },
            { data: 'status_badge', name: 'active', orderable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[1, 'asc']],
        pageLength: 25,
        language: {
            url: "{{ asset('assets/libs/datatables.net/js/Indonesian.json') }}"
        }
    });

    // Filter handlers
    $('#filter-type, #filter-active, #filter-low-stock').on('change', function() {
        table.draw();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter-type, #filter-active, #filter-low-stock').val('');
        table.draw();
    });

    // Create button
    $('#btn-create-inventory').on('click', function() {
        $.get("{{ route('inventory.create') }}", function(response) {
            $('#inventoryModalBody').html(response.html);
            $('#inventoryModal').find('.modal-title').text('Tambah Item Inventory Baru');
            $('#inventoryModal').modal('show');
        });
    });

    // Edit button
    $(document).on('click', '.btn-edit-inventory', function() {
        let itemId = $(this).data('item-id');
        $.get("{{ url('inventory') }}/" + itemId + "/edit", function(response) {
            $('#inventoryModalBody').html(response.html);
            $('#inventoryModal').find('.modal-title').text('Edit Item Inventory');
            $('#inventoryModal').modal('show');
        });
    });

    // Show button
    $(document).on('click', '.btn-show-inventory', function() {
        let itemId = $(this).data('item-id');
        $.get("{{ url('inventory') }}/" + itemId, function(response) {
            $('#inventoryModalBody').html(response.html);
            $('#inventoryModal').find('.modal-title').text('Detail Item Inventory');
            $('#inventoryModal').modal('show');
        });
    });

    // Use item button
    $(document).on('click', '.btn-use-inventory', function() {
        let itemId = $(this).data('item-id');
        $.get("{{ url('inventory') }}/" + itemId + "/use", function(response) {
            $('#inventoryModalBody').html(response.html);
            $('#inventoryModal').find('.modal-title').text('Gunakan Item');
            $('#inventoryModal').modal('show');
        });
    });

    // Restock button
    $(document).on('click', '.btn-restock-inventory', function() {
        let itemId = $(this).data('item-id');
        let itemName = $(this).data('item-name');
        
        Swal.fire({
            title: 'Tambah Stok',
            html: `
                <div class="mb-3">
                    <label class="form-label">Jumlah</label>
                    <input type="number" class="form-control" id="restock-quantity" min="1" value="1" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Catatan (Opsional)</label>
                    <textarea class="form-control" id="restock-notes" rows="2"></textarea>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Tambah Stok',
            cancelButtonText: 'Batal',
            preConfirm: () => {
                return {
                    quantity: parseInt(document.getElementById('restock-quantity').value),
                    notes: document.getElementById('restock-notes').value
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('inventory') }}/" + itemId + "/restock",
                    method: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        quantity: result.value.quantity,
                        notes: result.value.notes
                    },
                    success: function(response) {
                        Toast.success(response.message);
                        table.draw();
                        $('#inventoryModal').modal('hide');
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            Toast.error(xhr.responseJSON.message || 'Validasi gagal');
                        } else {
                            Toast.error('Terjadi kesalahan');
                        }
                    }
                });
            }
        });
    });

    // Delete button
    $(document).on('click', '.btn-delete-inventory', function() {
        let itemId = $(this).data('item-id');
        let itemName = $(this).data('item-name');
        
        Swal.fire({
            title: 'Hapus Item?',
            text: `Apakah Anda yakin ingin menghapus "${itemName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('inventory') }}/" + itemId,
                    method: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        Toast.success(response.message);
                        table.draw();
                    },
                    error: function() {
                        Toast.error('Terjadi kesalahan saat menghapus');
                    }
                });
            }
        });
    });

    // Form submit
    $(document).on('click', '#btn-submit-form', function() {
        let form = $('#inventory-form');
        if (form.length === 0) {
            form = $('#use-inventory-form');
        }
        
        let formData = new FormData(form[0]);
        let url = form.attr('action');
        let method = form.find('input[name="_method"]').val() || 'POST';

        $.ajax({
            url: url,
            method: method,
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                Toast.success(response.message);
                table.draw();
                $('#inventoryModal').modal('hide');
            },
            error: function(xhr) {
                if (xhr.status === 422) {
                    let errors = xhr.responseJSON.errors;
                    form.find('.is-invalid').removeClass('is-invalid');
                    form.find('.invalid-feedback').addClass('d-none');
                    
                    $.each(errors, function(key, value) {
                        let field = form.find('[name="' + key + '"]');
                        field.addClass('is-invalid');
                        let errorDiv = field.siblings('.invalid-feedback');
                        if (errorDiv.length === 0) {
                            errorDiv = field.parent().find('.invalid-feedback');
                        }
                        errorDiv.removeClass('d-none').text(value[0]);
                    });
                } else {
                    Toast.error(xhr.responseJSON?.message || 'Terjadi kesalahan');
                }
            }
        });
    });
});
</script>
@endpush
@endsection

