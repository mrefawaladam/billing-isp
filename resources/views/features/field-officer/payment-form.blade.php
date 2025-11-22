@extends('layouts.app')

@section('title', 'Form Pembayaran')

@push('styles')
<style>
    .preview-image {
        max-width: 100%;
        max-height: 300px;
        border-radius: 8px;
        margin-top: 10px;
        border: 1px solid #ddd;
        padding: 5px;
        background: #f8f9fa;
    }
    .upload-area {
        border: 2px dashed #ddd;
        border-radius: 8px;
        padding: 30px 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s;
        background-color: #fafafa;
        min-height: 150px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .upload-area:hover {
        border-color: #0d6efd;
        background-color: #f0f7ff;
    }
    .upload-area.dragover {
        border-color: #0d6efd;
        background-color: #e7f1ff;
    }
    .upload-area i {
        font-size: 3rem;
        margin-bottom: 10px;
    }
    #transfer-proof-preview,
    #field-photo-preview {
        margin-top: 15px;
        padding: 15px;
        border: 1px solid #ddd;
        border-radius: 8px;
        background: #f8f9fa;
    }
</style>
@endpush

@section('content')
<x-layout.page-header
    title="Form Pembayaran"
    :breadcrumb-title="'Form Pembayaran'"
    :breadcrumb-items="[
        ['label' => 'Daftar Pelanggan', 'url' => route('field-officer.customers')],
        ['label' => $invoice->customer->name, 'url' => route('field-officer.customers.show', $invoice->customer)],
        ['label' => 'Pembayaran']
    ]"
/>

<!-- Toast Notification -->
<x-ui.toast-notification />

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-4">Informasi Tagihan</h5>
                <table class="table table-borderless">
                    <tr>
                        <td width="200"><strong>No. Tagihan:</strong></td>
                        <td>{{ $invoice->invoice_number }}</td>
                    </tr>
                    <tr>
                        <td><strong>Pelanggan:</strong></td>
                        <td>{{ $invoice->customer->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Periode:</strong></td>
                        <td>
                            {{ \Carbon\Carbon::create()->month($invoice->month)->locale('id')->monthName }} {{ $invoice->year }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Jatuh Tempo:</strong></td>
                        <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Total Tagihan:</strong></td>
                        <td><h4 class="text-primary mb-0">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</h4></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body">
                <h5 class="card-title mb-4">Form Pembayaran</h5>
                <form id="payment-form" enctype="multipart/form-data">
                    @csrf

                    <!-- Payment Method -->
                    <div class="mb-4">
                        <label class="form-label"><strong>Metode Pembayaran <span class="text-danger">*</span></strong></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="method" id="method-cash" value="cash" checked>
                                    <label class="form-check-label" for="method-cash">
                                        <i class="ti ti-cash me-1"></i> Cash
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="method" id="method-transfer" value="transfer">
                                    <label class="form-check-label" for="method-transfer">
                                        <i class="ti ti-transfer me-1"></i> Transfer
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transfer Proof (only for transfer method) -->
                    <div class="mb-4" id="transfer-proof-section" style="display: none;">
                        <label class="form-label"><strong>Bukti Transfer <span class="text-danger">*</span></strong></label>
                        <label for="transfer_proof" class="upload-area" id="transfer-proof-upload" style="cursor: pointer;">
                            <i class="ti ti-upload text-primary"></i>
                            <p class="mt-2 mb-1 fw-medium">Klik atau drag & drop untuk upload bukti transfer</p>
                            <p class="text-muted small mb-0">Format: JPG, PNG, GIF (Max: 5MB)</p>
                            <input type="file" name="transfer_proof" id="transfer_proof" accept="image/*" style="display: none;">
                        </label>
                        <div id="transfer-proof-preview" style="display: none;">
                            <div class="text-center">
                                <img id="transfer-proof-img" class="preview-image" alt="Preview Bukti Transfer">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-danger" id="remove-transfer-proof">
                                        <i class="ti ti-x me-1"></i> Hapus Foto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Field Photo (Optional) -->
                    <div class="mb-4">
                        <label class="form-label"><strong>Foto Lapangan (Opsional)</strong></label>
                        <small class="text-muted d-block mb-2">Foto bukti pembayaran atau rumah pelanggan saat dikunjungi</small>
                        <label for="field_photo" class="upload-area" id="field-photo-upload" style="cursor: pointer;">
                            <i class="ti ti-camera text-info"></i>
                            <p class="mt-2 mb-1 fw-medium">Klik atau drag & drop untuk upload foto</p>
                            <p class="text-muted small mb-0">Format: JPG, PNG, GIF (Max: 5MB)</p>
                            <input type="file" name="field_photo" id="field_photo" accept="image/*" style="display: none;">
                        </label>
                        <div id="field-photo-preview" style="display: none;">
                            <div class="text-center">
                                <img id="field-photo-img" class="preview-image" alt="Preview Foto Lapangan">
                                <div class="mt-2">
                                    <button type="button" class="btn btn-sm btn-danger" id="remove-field-photo">
                                        <i class="ti ti-x me-1"></i> Hapus Foto
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Note -->
                    <div class="mb-4">
                        <label for="note" class="form-label"><strong>Catatan (Opsional)</strong></label>
                        <textarea class="form-control" name="note" id="note" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>

                    <!-- Submit Button -->
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success" id="btn-submit">
                            <i class="ti ti-check me-1"></i> Konfirmasi Pembayaran
                        </button>
                        <a href="{{ route('field-officer.customers.show', $invoice->customer) }}" class="btn btn-secondary">
                            <i class="ti ti-x me-1"></i> Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Riwayat Pembayaran</h5>
                @if($invoice->payments->count() > 0)
                    <div class="list-group">
                        @foreach($invoice->payments->sortByDesc('created_at') as $payment)
                            <div class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center mb-2">
                                            <span class="badge bg-info me-2">{{ strtoupper($payment->method) }}</span>
                                            <strong class="text-primary">Rp {{ number_format($payment->amount, 0, ',', '.') }}</strong>
                                        </div>
                                        <div class="mb-2">
                                            <small class="text-muted d-block">
                                                <i class="ti ti-calendar me-1"></i>
                                                {{ $payment->paid_date ? $payment->paid_date->format('d/m/Y') : '-' }}
                                            </small>
                                            @if($payment->created_at)
                                                <small class="text-muted d-block">
                                                    <i class="ti ti-clock me-1"></i>
                                                    {{ $payment->created_at->format('H:i') }}
                                                </small>
                                            @endif
                                        </div>
                                        @if($payment->receivedBy)
                                            <div class="border-top pt-2">
                                                <small class="text-muted d-block mb-1">
                                                    <i class="ti ti-user me-1"></i> Diproses oleh:
                                                </small>
                                                <strong class="text-success">{{ $payment->receivedBy->name }}</strong>
                                            </div>
                                        @endif
                                        @if($payment->note)
                                            <div class="mt-2">
                                                <small class="text-muted d-block mb-1">Catatan:</small>
                                                <small>{{ $payment->note }}</small>
                                            </div>
                                        @endif
                                    </div>
                                    @if($payment->transfer_proof_url)
                                        <a href="{{ $payment->transfer_proof_url }}" target="_blank" class="btn btn-sm btn-outline-primary ms-2" title="Lihat Bukti Transfer">
                                            <i class="ti ti-photo"></i>
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">Belum ada riwayat pembayaran</p>
                @endif
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure jQuery is loaded
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }

    const $ = jQuery;

    // Toggle transfer proof section based on payment method
    $('input[name="method"]').on('change', function() {
        if ($(this).val() === 'transfer') {
            $('#transfer-proof-section').slideDown(300);
        } else {
            $('#transfer-proof-section').slideUp(300);
        }
    });

    // Handle file upload for transfer proof - using label, no need for click handler
    // The label will automatically trigger the file input

    $('#transfer_proof').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            if (file.size > 5 * 1024 * 1024) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Ukuran file maksimal 5MB');
                } else {
                    alert('Ukuran file maksimal 5MB');
                }
                $(this).val('');
                return;
            }

            // Validate file type
            if (!file.type.match('image.*')) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('File harus berupa gambar');
                } else {
                    alert('File harus berupa gambar');
                }
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#transfer-proof-img').attr('src', e.target.result);
                $('#transfer-proof-preview').show();
                $('#transfer-proof-upload').hide();
            };
            reader.onerror = function() {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Gagal membaca file');
                } else {
                    alert('Gagal membaca file');
                }
            };
            reader.readAsDataURL(file);
        }
    });

    $('#remove-transfer-proof').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#transfer_proof').val('');
        $('#transfer-proof-preview').hide();
        $('#transfer-proof-upload').show();
    });

    // Handle file upload for field photo - using label, no need for click handler
    // The label will automatically trigger the file input

    $('#field_photo').on('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            // Validate file size
            if (file.size > 5 * 1024 * 1024) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Ukuran file maksimal 5MB');
                } else {
                    alert('Ukuran file maksimal 5MB');
                }
                $(this).val('');
                return;
            }

            // Validate file type
            if (!file.type.match('image.*')) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('File harus berupa gambar');
                } else {
                    alert('File harus berupa gambar');
                }
                $(this).val('');
                return;
            }

            const reader = new FileReader();
            reader.onload = function(e) {
                $('#field-photo-img').attr('src', e.target.result);
                $('#field-photo-preview').show();
                $('#field-photo-upload').hide();
            };
            reader.onerror = function() {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Gagal membaca file');
                } else {
                    alert('Gagal membaca file');
                }
            };
            reader.readAsDataURL(file);
        }
    });

    $('#remove-field-photo').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#field_photo').val('');
        $('#field-photo-preview').hide();
        $('#field-photo-upload').show();
    });

    // Handle drag and drop
    ['transfer-proof-upload', 'field-photo-upload'].forEach(id => {
        const uploadArea = document.getElementById(id);
        const input = document.getElementById(id === 'transfer-proof-upload' ? 'transfer_proof' : 'field_photo');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.add('dragover');
            }, false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            uploadArea.addEventListener(eventName, () => {
                uploadArea.classList.remove('dragover');
            }, false);
        });

        uploadArea.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            if (files.length > 0) {
                input.files = files;
                $(input).trigger('change');
            }
        }
    });

    // Handle form submission
    $('#payment-form').on('submit', function(e) {
        e.preventDefault();

        const method = $('input[name="method"]:checked').val();
        const transferProof = $('#transfer_proof')[0].files[0];

        // Validation
        if (method === 'transfer' && !transferProof) {
            Toast.error('Bukti transfer wajib diunggah untuk pembayaran transfer.');
            return;
        }

        const formData = new FormData(this);
        // Add invoice_id to form data
        formData.append('invoice_id', '{{ $invoice->id }}');

        const btn = $('#btn-submit');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Memproses...');

        $.ajax({
            url: "{{ route('field-officer.invoices.payment.process', $invoice) }}",
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
                    setTimeout(function() {
                        window.location.href = "{{ route('field-officer.customers.show', $invoice->customer) }}";
                    }, 1500);
                } else {
                    Toast.error(response.message || 'Gagal memproses pembayaran.');
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
                    Toast.error(response?.message || 'Gagal memproses pembayaran.');
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="ti ti-check me-1"></i> Konfirmasi Pembayaran');
            }
        });
    });
});
</script>
@endpush
@endsection

