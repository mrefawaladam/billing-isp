<div class="d-flex gap-2">
    <button type="button" class="btn btn-sm btn-info btn-show-invoice" data-invoice-id="{{ $invoice->id }}" title="Lihat Detail">
        <i class="ti ti-eye"></i>
    </button>
    <a href="{{ route('invoices.print', $invoice) }}" target="_blank" class="btn btn-sm btn-primary btn-print-invoice" data-invoice-id="{{ $invoice->id }}" title="Cetak PDF">
        <i class="ti ti-printer"></i>
    </a>
    @if($invoice->status !== 'PAID' && $invoice->customer && $invoice->customer->phone)
        <button type="button" class="btn btn-sm btn-success btn-send-whatsapp-invoice" data-invoice-id="{{ $invoice->id }}" title="Kirim Notifikasi WhatsApp">
            <i class="ti ti-brand-whatsapp"></i>
        </button>
    @endif
    @if($invoice->status !== 'PAID')
        <button type="button" class="btn btn-sm btn-success btn-mark-paid" data-invoice-id="{{ $invoice->id }}" title="Tandai Sudah Dibayar">
            <i class="ti ti-check"></i>
        </button>
    @endif
</div>

