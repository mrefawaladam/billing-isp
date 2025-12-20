<div class="row">
    <div class="col-md-6">
        <h5 class="mb-3">Informasi Notifikasi</h5>
        <table class="table table-bordered">
            <tr>
                <th width="40%">Status</th>
                <td>
                    @if($whatsapp->status === 'sent')
                        <span class="badge bg-success">Berhasil</span>
                    @elseif($whatsapp->status === 'failed')
                        <span class="badge bg-danger">Gagal</span>
                    @else
                        <span class="badge bg-warning">Pending</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Pelanggan</th>
                <td>{{ $whatsapp->customer ? $whatsapp->customer->name : '-' }}</td>
            </tr>
            <tr>
                <th>No. Tagihan</th>
                <td>{{ $whatsapp->invoice ? $whatsapp->invoice->invoice_number : '-' }}</td>
            </tr>
            <tr>
                <th>Nomor WhatsApp</th>
                <td>{{ $whatsapp->phone }}</td>
            </tr>
            <tr>
                <th>Template</th>
                <td>{{ ucfirst($whatsapp->template_name ?? 'Manual') }}</td>
            </tr>
            <tr>
                <th>Dijadwalkan</th>
                <td>{{ $whatsapp->scheduled_at ? $whatsapp->scheduled_at->format('d/m/Y H:i:s') : '-' }}</td>
            </tr>
            <tr>
                <th>Dikirim</th>
                <td>{{ $whatsapp->sent_at ? $whatsapp->sent_at->format('d/m/Y H:i:s') : '-' }}</td>
            </tr>
            @if($whatsapp->error_message)
            <tr>
                <th>Error</th>
                <td class="text-danger">{{ $whatsapp->error_message }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="col-md-6">
        <h5 class="mb-3">Pesan</h5>
        <div class="card">
            <div class="card-body">
                <pre style="white-space: pre-wrap; font-family: inherit;">{{ $whatsapp->message_text }}</pre>
            </div>
        </div>

        @if($whatsapp->provider_response)
        <h5 class="mb-3 mt-4">Response dari API</h5>
        <div class="card">
            <div class="card-body">
                <pre style="white-space: pre-wrap; font-family: monospace; font-size: 12px;">{{ json_encode(json_decode($whatsapp->provider_response), JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
        @endif
    </div>
</div>

@if($whatsapp->status === 'failed')
<div class="mt-3">
    <button type="button" class="btn btn-success btn-resend-whatsapp" data-notification-id="{{ $whatsapp->id }}">
        <i class="ti ti-refresh me-1"></i> Kirim Ulang
    </button>
</div>

<script>
$(document).ready(function() {
    // Resend button handler in modal
    $('.btn-resend-whatsapp').on('click', function() {
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
                    sendResendRequestFromModal(notificationId, btn);
                }
            });
        } else {
            if (confirm('Apakah Anda yakin ingin mengirim ulang pesan ini?')) {
                sendResendRequestFromModal(notificationId, btn);
            }
        }
    });

    function sendResendRequestFromModal(notificationId, btn) {
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Mengirim...');
        
        $.ajax({
            url: "{{ url('whatsapp') }}/" + notificationId + "/resend",
            method: 'POST',
            data: { 
                _token: "{{ csrf_token() }}"
            },
            success: function(response) {
                if (response.success) {
                    if (typeof Toast !== 'undefined') {
                        Toast.success(response.message);
                    } else {
                        alert('Berhasil: ' + response.message);
                    }
                    // Reload page or close modal
                    setTimeout(function() {
                        location.reload();
                    }, 1500);
                } else {
                    if (typeof Toast !== 'undefined') {
                        Toast.error(response.message || 'Gagal mengirim ulang pesan');
                    } else {
                        alert('Gagal: ' + (response.message || 'Gagal mengirim ulang pesan'));
                    }
                }
            },
            error: function(xhr) {
                let errorMsg = 'Terjadi kesalahan';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                } else if (xhr.status === 404) {
                    errorMsg = 'Route tidak ditemukan';
                }
                if (typeof Toast !== 'undefined') {
                    Toast.error(errorMsg);
                } else {
                    alert('Error: ' + errorMsg);
                }
            },
            complete: function() {
                btn.prop('disabled', false).html('<i class="ti ti-refresh me-1"></i> Kirim Ulang');
            }
        });
    }
});
</script>
@endif

