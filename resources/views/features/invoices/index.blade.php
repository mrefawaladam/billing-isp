@extends('layouts.app')

@section('title', 'Manajemen Tagihan')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}" />
<style>
    #invoices-table_wrapper {
        overflow-x: auto;
    }
    #invoices-table {
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
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Manajemen Tagihan"
    :breadcrumb-title="'Manajemen Tagihan'"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<!-- DataTable -->
<div class="datatables">
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div>
                    <h4 class="card-title">Daftar Tagihan</h4>
                    <p class="card-subtitle mb-3">
                        Kelola tagihan bulanan, generate tagihan baru, dan lihat status pembayaran.
                    </p>
                </div>
                <div>
                    <button type="button" class="btn btn-success me-2" id="btn-generate-invoice">
                        <i class="ti ti-file-plus me-1"></i> Generate Tagihan
                    </button>
                    <button type="button" class="btn btn-info" id="btn-export-invoice">
                        <i class="ti ti-download me-1"></i> Export
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-3">
                <div class="col-md-2">
                    <label for="filter-status" class="form-label">Status</label>
                    <select id="filter-status" class="form-select">
                        <option value="">Semua</option>
                        <option value="UNPAID">Belum Dibayar</option>
                        <option value="PAID">Sudah Dibayar</option>
                        <option value="OVERDUE">Terlambat</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-year" class="form-label">Tahun</label>
                    <select id="filter-year" class="form-select">
                        <option value="">Semua</option>
                        @foreach($years as $year)
                            <option value="{{ $year }}" {{ $year == date('Y') ? 'selected' : '' }}>{{ $year }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="filter-month" class="form-label">Bulan</label>
                    <select id="filter-month" class="form-select">
                        <option value="">Semua</option>
                        <option value="1">Januari</option>
                        <option value="2">Februari</option>
                        <option value="3">Maret</option>
                        <option value="4">April</option>
                        <option value="5">Mei</option>
                        <option value="6">Juni</option>
                        <option value="7">Juli</option>
                        <option value="8">Agustus</option>
                        <option value="9">September</option>
                        <option value="10">Oktober</option>
                        <option value="11">November</option>
                        <option value="12">Desember</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-secondary w-100" id="btn-reset-filter">
                        <i class="ti ti-refresh me-1"></i> Reset Filter
                    </button>
                </div>
            </div>

            <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                <table id="invoices-table" class="table table-striped table-bordered align-middle" style="min-width: 1200px;">
                    <thead>
                        <tr>
                            <th style="min-width: 120px;">No. Tagihan</th>
                            <th style="min-width: 100px;">Kode</th>
                            <th style="min-width: 150px;">Nama Pelanggan</th>
                            <th style="min-width: 120px;">Periode</th>
                            <th style="min-width: 120px;">Jatuh Tempo</th>
                            <th style="min-width: 130px;">Total Tagihan</th>
                            <th style="min-width: 150px;">Status</th>
                            <th style="min-width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Generate Invoice Modal -->
<x-ui.modal
    id="generateInvoiceModal"
    title="Generate Tagihan Bulanan"
    size="lg"
    content-id="generateInvoiceModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button type="button" class="btn btn-primary" id="btn-submit-generate">Generate</button>
    </x-slot>
</x-ui.modal>

<!-- Invoice Detail Modal -->
<x-ui.modal
    id="invoiceModal"
    title="Detail Tagihan"
    size="xl"
    content-id="invoiceModalBody"
>
    <x-slot name="footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
        <button type="button" class="btn btn-primary" id="btn-print-invoice-modal">
            <i class="ti ti-printer me-1"></i> Cetak Ulang Invoice
        </button>
    </x-slot>
</x-ui.modal>

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
@if(!isset($jqueryLoaded))
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    @php $jqueryLoaded = true; @endphp
@endif
<script src="{{ asset('assets/libs/datatables.net/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js') }}"></script>
<script>
$(document).ready(function() {
    // Check if DataTable is already initialized
    if ($.fn.DataTable.isDataTable('#invoices-table')) {
        $('#invoices-table').DataTable().destroy();
    }

    // Initialize DataTable
    const invoicesTable = $('#invoices-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('invoices.index') }}",
            data: function(d) {
                var status = $('#filter-status').val();
                var year = $('#filter-year').val();
                var month = $('#filter-month').val();

                if (status) d.status = status;
                if (year) d.year = year;
                if (month) d.month = month;
            }
        },
        columns: [
            { data: 'invoice_number', name: 'invoice_number' },
            { data: 'customer_code', name: 'customer_code' },
            { data: 'customer_name', name: 'customer_name' },
            { data: 'period', name: 'period' },
            { data: 'due_date_formatted', name: 'due_date' },
            { data: 'total_amount_formatted', name: 'total_amount' },
            { data: 'status_badge', name: 'status', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ],
        pageLength: 10,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        responsive: false,
        scrollX: true,
        scrollCollapse: true,
        autoWidth: false,
        order: [[4, 'desc']],
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
    $('#filter-status, #filter-year, #filter-month').on('change', function() {
        invoicesTable.ajax.reload();
    });

    // Reset filters
    $('#btn-reset-filter').on('click', function() {
        $('#filter-status, #filter-year, #filter-month').val('');
        invoicesTable.ajax.reload();
    });

    // Load generate form
    $('#btn-generate-invoice').on('click', function() {
        Modal.load('generateInvoiceModal', "{{ route('invoices.create') }}", 'Generate Tagihan Bulanan');
    });

    // Handle generate form submission
    $('#btn-submit-generate').on('click', function() {
        Form.submit('#generate-invoice-form', {
            success: function(response) {
                if (response.success) {
                    Modal.hide('generateInvoiceModal');
                    Toast.success(response.message);
                    invoicesTable.ajax.reload(null, false);
                }
            }
        });
    });

    // Store current invoice ID for print
    let currentInvoiceIdForPrint = null;

    // Handle show button click
    $(document).on('click', '.btn-show-invoice', function(e) {
        e.preventDefault();
        const invoiceId = $(this).data('invoice-id');
        currentInvoiceIdForPrint = invoiceId;
        Modal.load('invoiceModal', `/invoices/${invoiceId}`, 'Detail Tagihan');
    });

    // Handle print button in modal
    $('#btn-print-invoice-modal').on('click', function() {
        if (currentInvoiceIdForPrint) {
            window.open(`/invoices/${currentInvoiceIdForPrint}/print`, '_blank');
        } else {
            Toast.error('Invoice ID tidak ditemukan.');
        }
    });

    // Handle print button click
    $(document).on('click', '.btn-print-invoice', function(e) {
        e.preventDefault();
        const invoiceId = $(this).data('invoice-id');
        window.open(`/invoices/${invoiceId}/print`, '_blank');
    });

    // Handle export button click
    $('#btn-export-invoice').on('click', function() {
        Toast.info('Fitur export sedang dalam pengembangan.');
    });

    // Handle mark as paid button click
    let currentInvoiceId = null;
    $(document).on('click', '.btn-mark-paid', function(e) {
        e.preventDefault();
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
                    invoicesTable.ajax.reload(null, false);
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

    // Handle send WhatsApp notification button
    $(document).on('click', '.btn-send-whatsapp-invoice', function(e) {
        e.preventDefault();
        const invoiceId = $(this).data('invoice-id');
        
        Swal.fire({
            title: 'Kirim Notifikasi WhatsApp?',
            text: 'Apakah Anda yakin ingin mengirim notifikasi tagihan via WhatsApp?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya, Kirim',
            cancelButtonText: 'Batal',
            confirmButtonColor: '#25D366'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/whatsapp/invoices/${invoiceId}/send`,
                    method: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content') || $('input[name="_token"]').val()
                    },
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(response) {
                        if (response.success) {
                            Toast.success(response.message);
                        } else {
                            Toast.error(response.message || 'Gagal mengirim notifikasi.');
                        }
                    },
                    error: function(xhr) {
                        const response = xhr.responseJSON;
                        Toast.error(response?.message || 'Gagal mengirim notifikasi.');
                    }
                });
            }
        });
    });

    // Reset form when modal is hidden
    $('#generateInvoiceModal, #invoiceModal, #confirmPaymentModal').on('hidden.bs.modal', function() {
        Modal.clear($(this).attr('id'));
        currentInvoiceId = null;
    });
});
</script>
@endpush
@endsection

