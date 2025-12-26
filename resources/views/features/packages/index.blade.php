@extends('layouts.app')

@section('title', 'Manajemen Paket Internet')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<style>
    #packages-table_wrapper {
        overflow-x: auto;
    }
    #packages-table {
        width: 100% !important;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Manajemen Paket Internet"
    :breadcrumb-title="'Manajemen Paket Internet'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Daftar Paket Internet</h4>
                    <p class="card-subtitle mb-3">
                        Kelola paket internet, tambah, edit, dan hapus paket dari halaman ini.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-create-package">
                    <i class="ti ti-plus me-1"></i> Tambah Paket Baru
                </button>
            </div>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-4">
                    <label for="filter-service-type" class="form-label">Type Layanan</label>
                    <select id="filter-service-type" class="form-select">
                        <option value="">Semua</option>
                        <option value="Dedicated Internet">Dedicated Internet</option>
                        <option value="Internet Broadband">Internet Broadband</option>
                        <option value="Wireless">Wireless</option>
                        <option value="Fiber">Fiber</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="filter-active" class="form-label">Status</label>
                    <select id="filter-active" class="form-select">
                        <option value="">Semua</option>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary w-100" id="btn-reset-filter">
                        <i class="ti ti-refresh me-1"></i> Reset Filter
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="packages-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Paket</th>
                            <th>Kode Paket</th>
                            <th>Kecepatan</th>
                            <th>Type Layanan</th>
                            <th>Harga</th>
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
    id="packageModal"
    title="Tambah Paket Baru"
    size="lg"
    content-id="packageModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-submit-form">Simpan</button>
    </x-slot>
</x-ui.modal>

@push('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    let table = $('#packages-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('packages.index') }}",
            data: function(d) {
                d.service_type = $('#filter-service-type').val();
                d.active = $('#filter-active').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, width: '50px' },
            { data: 'name', name: 'name' },
            { data: 'package_code', name: 'package_code' },
            { data: 'speed', name: 'speed' },
            { data: 'service_type', name: 'service_type' },
            { data: 'price_formatted', name: 'price', orderable: false },
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
    $('#filter-service-type, #filter-active').on('change', function() {
        table.draw();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter-service-type, #filter-active').val('');
        table.draw();
    });

    // Create button
    $('#btn-create-package').on('click', function() {
        $.get("{{ route('packages.create') }}", function(response) {
            $('#packageModalBody').html(response.html);
            $('#packageModal').find('.modal-title').text('Tambah Paket Baru');
            $('#packageModal').modal('show');
        });
    });

    // Edit button
    $(document).on('click', '.btn-edit-package', function() {
        let packageId = $(this).data('package-id');
        $.get("{{ url('packages') }}/" + packageId + "/edit", function(response) {
            $('#packageModalBody').html(response.html);
            $('#packageModal').find('.modal-title').text('Edit Paket');
            $('#packageModal').modal('show');
        });
    });

    // Show button
    $(document).on('click', '.btn-show-package', function() {
        let packageId = $(this).data('package-id');
        $.get("{{ url('packages') }}/" + packageId, function(response) {
            $('#packageModalBody').html(response.html);
            $('#packageModal').find('.modal-title').text('Detail Paket');
            $('#packageModal').modal('show');
        });
    });

    // Delete button
    $(document).on('click', '.btn-delete-package', function() {
        let packageId = $(this).data('package-id');
        let packageName = $(this).data('package-name');
        
        Swal.fire({
            title: 'Hapus Paket?',
            text: `Apakah Anda yakin ingin menghapus "${packageName}"?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('packages') }}/" + packageId,
                    method: 'DELETE',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(response) {
                        if (response.success) {
                            Toast.success(response.message);
                            table.draw();
                        } else {
                            Toast.error(response.message);
                        }
                    },
                    error: function(xhr) {
                        Toast.error(xhr.responseJSON?.message || 'Terjadi kesalahan saat menghapus');
                    }
                });
            }
        });
    });

    // Form submit
    $(document).on('click', '#btn-submit-form', function() {
        let form = $('#package-form');
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
                if (response.success) {
                    Toast.success(response.message);
                    table.draw();
                    $('#packageModal').modal('hide');
                } else {
                    Toast.error(response.message);
                }
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

