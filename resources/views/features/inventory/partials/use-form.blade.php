<form id="use-inventory-form" action="{{ $formAction }}" method="POST">
    @csrf

    <div class="alert alert-info">
        <strong>Stok Tersedia:</strong> {{ $item->stock_quantity }} {{ $item->unit }}
    </div>

    <div class="mb-3">
        <label for="quantity" class="form-label">Jumlah <span class="text-danger">*</span></label>
        <input
            type="number"
            class="form-control"
            id="quantity"
            name="quantity"
            min="1"
            max="{{ $item->stock_quantity }}"
            value="1"
            required
        >
        <div class="invalid-feedback d-none" id="quantity-error"></div>
    </div>

    <div class="mb-3">
        <label for="usage_type" class="form-label">Jenis Penggunaan <span class="text-danger">*</span></label>
        <select class="form-select" id="usage_type" name="usage_type" required>
            <option value="">Pilih Jenis</option>
            <option value="installed">Dipasang</option>
            <option value="maintenance">Maintenance</option>
            <option value="damaged">Rusak</option>
            <option value="lost">Hilang</option>
        </select>
        <div class="invalid-feedback d-none" id="usage_type-error"></div>
    </div>

    <div class="mb-3">
        <label for="customer_id" class="form-label">Pelanggan (Opsional)</label>
        <select class="form-select" id="customer_id" name="customer_id">
            <option value="">Pilih Pelanggan</option>
            @foreach($customers as $customer)
                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->customer_code }})</option>
            @endforeach
        </select>
        <div class="invalid-feedback d-none" id="customer_id-error"></div>
    </div>

    <div class="mb-3">
        <label for="device_id" class="form-label">Perangkat (Opsional)</label>
        <select class="form-select" id="device_id" name="device_id">
            <option value="">Pilih Perangkat</option>
            @foreach($devices as $device)
                <option value="{{ $device->id }}" data-customer-id="{{ $device->customer_id }}">
                    {{ $device->name }} - {{ $device->customer->name }}
                </option>
            @endforeach
        </select>
        <div class="invalid-feedback d-none" id="device_id-error"></div>
    </div>

    <div class="mb-3">
        <label for="notes" class="form-label">Catatan</label>
        <textarea
            class="form-control"
            id="notes"
            name="notes"
            rows="3"
            placeholder="Catatan penggunaan item..."
        ></textarea>
        <div class="invalid-feedback d-none" id="notes-error"></div>
    </div>

    <input type="hidden" name="inventory_item_id" value="{{ $item->id }}">
</form>

<script>
$(document).ready(function() {
    // Filter devices based on selected customer
    $('#customer_id').on('change', function() {
        let customerId = $(this).val();
        let deviceSelect = $('#device_id');
        
        if (customerId) {
            deviceSelect.find('option').each(function() {
                if ($(this).val() && $(this).data('customer-id') != customerId) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        } else {
            deviceSelect.find('option').show();
        }
        deviceSelect.val('');
    });
});
</script>

