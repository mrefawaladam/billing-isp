@extends('layouts.app')

@section('title', 'Notifikasi WhatsApp')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<style>
    #whatsapp-table_wrapper {
        overflow-x: auto;
    }
    #whatsapp-table {
        width: 100% !important;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Notifikasi WhatsApp"
    :breadcrumb-title="'Notifikasi WhatsApp'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Riwayat Notifikasi WhatsApp</h4>
                    <p class="card-subtitle mb-3">
                        Lihat riwayat dan kirim pesan WhatsApp dari halaman ini.
                    </p>
                </div>
                <button type="button" class="btn btn-primary" id="btn-send-whatsapp">
                    <i class="ti ti-brand-whatsapp me-1"></i> Kirim Pesan
                </button>
            </div>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="filter-status" class="form-label">Status</label>
                    <select id="filter-status" class="form-select">
                        <option value="">Semua</option>
                        <option value="sent">Berhasil</option>
                        <option value="failed">Gagal</option>
                        <option value="pending">Pending</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="filter-customer" class="form-label">Pelanggan</label>
                    <select id="filter-customer" class="form-select">
                        <option value="">Semua</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-date-from" class="form-label">Dari Tanggal</label>
                    <input type="date" id="filter-date-from" class="form-control">
                </div>
                <div class="col-md-2">
                    <label for="filter-date-to" class="form-label">Sampai Tanggal</label>
                    <input type="date" id="filter-date-to" class="form-control">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary w-100" id="btn-reset-filter">
                        <i class="ti ti-refresh me-1"></i> Reset
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table id="whatsapp-table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>No. Tagihan</th>
                            <th>Nomor HP</th>
                            <th>Pesan</th>
                            <th>Status</th>
                            <th>Dikirim</th>
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
    id="whatsappModal"
    title="Kirim Pesan WhatsApp"
    size="lg"
    content-id="whatsappModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-submit-form">
            <i class="ti ti-brand-whatsapp me-1"></i> Kirim Pesan
        </button>
    </x-slot>
</x-ui.modal>

@push('scripts')
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script src="{{ asset('assets/libs/sweetalert2/dist/sweetalert2.all.min.js') }}"></script>
<script>
$(document).ready(function() {
    let table = $('#whatsapp-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('whatsapp.index') }}",
            data: function(d) {
                d.status = $('#filter-status').val();
                d.customer_id = $('#filter-customer').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            { data: 'created_at', name: 'created_at' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'phone', name: 'phone' },
            { data: 'message_preview', name: 'message_text', orderable: false },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'sent_at', name: 'sent_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        order: [[0, 'desc']],
        pageLength: 25,
        language: {
            url: "{{ asset('assets/libs/datatables.net/js/Indonesian.json') }}"
        }
    });

    // Filter handlers
    $('#filter-status, #filter-customer, #filter-date-from, #filter-date-to').on('change', function() {
        table.draw();
    });

    $('#btn-reset-filter').on('click', function() {
        $('#filter-status, #filter-customer, #filter-date-from, #filter-date-to').val('');
        table.draw();
    });

    // Send button
    $('#btn-send-whatsapp').on('click', function() {
        $.get("{{ route('whatsapp.create') }}", function(response) {
            $('#whatsappModalBody').html(response.html);
            $('#whatsappModal').find('.modal-title').text('Kirim Pesan WhatsApp');
            $('#whatsappModal').modal('show');
        });
    });

    // Show button
    $(document).on('click', '.btn-show-whatsapp', function() {
        let notificationId = $(this).data('notification-id');
        $.get("{{ route('whatsapp.index') }}/" + notificationId, function(response) {
            $('#whatsappModalBody').html(response.html);
            $('#whatsappModal').find('.modal-title').text('Detail Notifikasi');
            $('#whatsappModal').modal('show');
        });
    });

    // Resend button
    $(document).on('click', '.btn-resend-whatsapp', function() {
        let notificationId = $(this).data('notification-id');
        let btn = $(this);
        
        // Check if Swal is available, otherwise use confirm
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Kirim Ulang Pesan?',
                text: 'Apakah Anda yakin ingin mengirim ulang pesan ini?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, Kirim Ulang',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#25D366'
            }).then((result) => {
                if (result.isConfirmed) {
                    sendResendRequest(notificationId, btn);
                }
            });
        } else {
            // Fallback to native confirm
            if (confirm('Apakah Anda yakin ingin mengirim ulang pesan ini?')) {
                sendResendRequest(notificationId, btn);
            }
        }
    });

    // Function to send resend request
    function sendResendRequest(notificationId, btn) {
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span>');
        
        $.ajax({
            url: "{{ url('whatsapp') }}/" + notificationId + "/resend",
            method: 'POST',
            data: { 
                _token: "{{ csrf_token() }}",
                _method: 'POST'
            },
            success: function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    table.draw();
                    $('#whatsappModal').modal('hide');
                } else {
                    Toast.error(response.message || 'Gagal mengirim ulang pesan');
                }
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMsg = 'Route tidak ditemukan. Pastikan route sudah benar.';
                } else if (xhr.status === 500) {
                    errorMsg = 'Server error. Silakan coba lagi.';
                }
                Toast.error(errorMsg);
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="ti ti-refresh"></i>');
            }
        });
    }

    // Form submit
    $(document).on('click', '#btn-submit-form', function() {
        let form = $('#whatsapp-form');
        let formData = new FormData(form[0]);

        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    Toast.success(response.message);
                    table.draw();
                    $('#whatsappModal').modal('hide');
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

    // Auto-fill message when invoice selected
    $(document).on('change', '#invoice_id', function() {
        let invoiceId = $(this).val();
        if (invoiceId) {
            // You can add logic here to auto-fill message based on invoice
            // For now, just update customer phone
            let selectedOption = $(this).find('option:selected');
            let customerPhone = selectedOption.data('customer-phone');
            if (customerPhone) {
                $('#phone').val(customerPhone);
            }
        }
    });

    // Auto-fill phone when customer selected
    $(document).on('change', '#customer_id', function() {
        let customerId = $(this).val();
        if (customerId) {
            let selectedOption = $(this).find('option:selected');
            let customerPhone = selectedOption.data('phone');
            if (customerPhone) {
                $('#phone').val(customerPhone);
            }
        }
    });
});
</script>
@endpush
@endsection

