@php
    $isEdit = isset($item) && $item !== null;
@endphp

<form id="inventory-form" action="{{ $formAction }}" method="POST" @if($isEdit) data-item-id="{{ $item->id }}" @endif>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="code" class="form-label">Kode Item</label>
                <input
                    type="text"
                    class="form-control"
                    id="code"
                    name="code"
                    value="{{ $isEdit ? $item->code : old('code') }}"
                    placeholder="Akan di-generate otomatis jika kosong"
                >
                <div class="invalid-feedback d-none" id="code-error"></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="type" class="form-label">Jenis Item <span class="text-danger">*</span></label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">Pilih Jenis</option>
                    <option value="router" {{ ($isEdit && $item->type == 'router') || old('type') == 'router' ? 'selected' : '' }}>Router</option>
                    <option value="ont" {{ ($isEdit && $item->type == 'ont') || old('type') == 'ont' ? 'selected' : '' }}>ONT</option>
                    <option value="kabel" {{ ($isEdit && $item->type == 'kabel') || old('type') == 'kabel' ? 'selected' : '' }}>Kabel</option>
                    <option value="connector" {{ ($isEdit && $item->type == 'connector') || old('type') == 'connector' ? 'selected' : '' }}>Connector</option>
                    <option value="switch" {{ ($isEdit && $item->type == 'switch') || old('type') == 'switch' ? 'selected' : '' }}>Switch</option>
                    <option value="access_point" {{ ($isEdit && $item->type == 'access_point') || old('type') == 'access_point' ? 'selected' : '' }}>Access Point</option>
                    <option value="lainnya" {{ ($isEdit && $item->type == 'lainnya') || old('type') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
                <div class="invalid-feedback d-none" id="type-error"></div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="name" class="form-label">Nama Item <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control"
            id="name"
            name="name"
            value="{{ $isEdit ? $item->name : old('name') }}"
            required
            placeholder="Contoh: Router TP-Link Archer C6"
        >
        <div class="invalid-feedback d-none" id="name-error"></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="brand" class="form-label">Brand</label>
                <input
                    type="text"
                    class="form-control"
                    id="brand"
                    name="brand"
                    value="{{ $isEdit ? $item->brand : old('brand') }}"
                    placeholder="Contoh: TP-Link"
                >
                <div class="invalid-feedback d-none" id="brand-error"></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="model" class="form-label">Model</label>
                <input
                    type="text"
                    class="form-control"
                    id="model"
                    name="model"
                    value="{{ $isEdit ? $item->model : old('model') }}"
                    placeholder="Contoh: Archer C6"
                >
                <div class="invalid-feedback d-none" id="model-error"></div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea
            class="form-control"
            id="description"
            name="description"
            rows="3"
            placeholder="Deskripsi item..."
        >{{ $isEdit ? $item->description : old('description') }}</textarea>
        <div class="invalid-feedback d-none" id="description-error"></div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="mb-3">
                <label for="stock_quantity" class="form-label">Stok Awal</label>
                <input
                    type="number"
                    class="form-control"
                    id="stock_quantity"
                    name="stock_quantity"
                    value="{{ $isEdit ? $item->stock_quantity : (old('stock_quantity') ?? 0) }}"
                    min="0"
                >
                <div class="invalid-feedback d-none" id="stock_quantity-error"></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="mb-3">
                <label for="min_stock" class="form-label">Stok Minimum</label>
                <input
                    type="number"
                    class="form-control"
                    id="min_stock"
                    name="min_stock"
                    value="{{ $isEdit ? $item->min_stock : (old('min_stock') ?? 0) }}"
                    min="0"
                    placeholder="Alert jika stok <= ini"
                >
                <div class="invalid-feedback d-none" id="min_stock-error"></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="mb-3">
                <label for="unit" class="form-label">Satuan</label>
                <select class="form-select" id="unit" name="unit">
                    <option value="pcs" {{ ($isEdit && $item->unit == 'pcs') || old('unit') == 'pcs' ? 'selected' : '' }}>Pcs</option>
                    <option value="meter" {{ ($isEdit && $item->unit == 'meter') || old('unit') == 'meter' ? 'selected' : '' }}>Meter</option>
                    <option value="roll" {{ ($isEdit && $item->unit == 'roll') || old('unit') == 'roll' ? 'selected' : '' }}>Roll</option>
                    <option value="box" {{ ($isEdit && $item->unit == 'box') || old('unit') == 'box' ? 'selected' : '' }}>Box</option>
                    <option value="pack" {{ ($isEdit && $item->unit == 'pack') || old('unit') == 'pack' ? 'selected' : '' }}>Pack</option>
                </select>
                <div class="invalid-feedback d-none" id="unit-error"></div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="price" class="form-label">Harga (Rp)</label>
                <input
                    type="number"
                    class="form-control"
                    id="price"
                    name="price"
                    value="{{ $isEdit ? $item->price : (old('price') ?? 0) }}"
                    min="0"
                    step="0.01"
                    placeholder="0"
                >
                <div class="invalid-feedback d-none" id="price-error"></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="location" class="form-label">Lokasi Penyimpanan</label>
                <input
                    type="text"
                    class="form-control"
                    id="location"
                    name="location"
                    value="{{ $isEdit ? $item->location : old('location') }}"
                    placeholder="Contoh: Gudang A, Rak 1"
                >
                <div class="invalid-feedback d-none" id="location-error"></div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <div class="form-check">
            <input
                class="form-check-input"
                type="checkbox"
                id="active"
                name="active"
                value="1"
                {{ ($isEdit && $item->active) || old('active') ? 'checked' : '' }}
            >
            <label class="form-check-label" for="active">
                Aktif
            </label>
        </div>
    </div>
</form>

