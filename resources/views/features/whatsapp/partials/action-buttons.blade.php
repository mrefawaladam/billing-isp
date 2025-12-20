<div class="d-flex gap-2 justify-content-center">
    <button type="button" class="btn btn-sm btn-info btn-show-whatsapp" data-notification-id="{{ $notification->id }}" title="Lihat Detail">
        <i class="ti ti-eye"></i>
    </button>
    @if($notification->status === 'failed')
        <button type="button" class="btn btn-sm btn-success btn-resend-whatsapp" data-notification-id="{{ $notification->id }}" title="Kirim Ulang">
            <i class="ti ti-refresh"></i>
        </button>
    @endif
</div>

