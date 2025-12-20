<form id="whatsapp-form" action="{{ route('whatsapp.store') }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="customer_id" class="form-label">Pilih Pelanggan (Opsional)</label>
                <select class="form-select" id="customer_id" name="customer_id">
                    <option value="">Pilih Pelanggan</option>
                    @foreach($customers as $customer)
                        <option value="{{ $customer->id }}" data-phone="{{ $customer->phone }}" {{ $selectedCustomer && $selectedCustomer->id == $customer->id ? 'selected' : '' }}>
                            {{ $customer->name }} ({{ $customer->phone }})
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih pelanggan untuk auto-fill nomor telepon</small>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="invoice_id" class="form-label">Pilih Tagihan (Opsional)</label>
                <select class="form-select" id="invoice_id" name="invoice_id">
                    <option value="">Pilih Tagihan</option>
                    @foreach($invoices as $invoice)
                        <option value="{{ $invoice->id }}" data-customer-phone="{{ $invoice->customer->phone }}" {{ $selectedInvoice && $selectedInvoice->id == $invoice->id ? 'selected' : '' }}>
                            {{ $invoice->invoice_number }} - {{ $invoice->customer->name }} (Rp {{ number_format($invoice->total_amount, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">Pilih tagihan untuk auto-fill nomor telepon</small>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="phone" class="form-label">Nomor WhatsApp <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control"
            id="phone"
            name="phone"
            value="{{ $selectedCustomer ? $selectedCustomer->phone : ($selectedInvoice ? $selectedInvoice->customer->phone : old('phone')) }}"
            placeholder="081234567890 atau 6281234567890"
            required
        >
        <small class="text-muted">Format: 081234567890 atau 6281234567890</small>
        <div class="invalid-feedback d-none" id="phone-error"></div>
    </div>

    <div class="mb-3">
        <label for="message" class="form-label">Pesan <span class="text-danger">*</span></label>
        <textarea
            class="form-control"
            id="message"
            name="message"
            rows="8"
            placeholder="Tulis pesan WhatsApp di sini..."
            required
        >{{ old('message') }}</textarea>
        <small class="text-muted">
            <strong>Tips:</strong> Gunakan *teks* untuk <strong>bold</strong>, _teks_ untuk <em>italic</em>, ~teks~ untuk <strike>strikethrough</strike>
        </small>
        <div class="invalid-feedback d-none" id="message-error"></div>
    </div>

    <div class="alert alert-info">
        <i class="ti ti-info-circle me-2"></i>
        <strong>Catatan:</strong> Pastikan nomor WhatsApp sudah terhubung dengan device Fonnte Anda.
    </div>
</form>

