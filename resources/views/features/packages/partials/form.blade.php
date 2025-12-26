@php
    $isEdit = isset($package) && $package !== null;
@endphp

<form id="package-form" action="{{ $formAction }}" method="POST" @if($isEdit) data-package-id="{{ $package->id }}" @endif>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="mb-3">
        <label for="name" class="form-label">Nama Paket <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control"
            id="name"
            name="name"
            value="{{ $isEdit ? $package->name : old('name') }}"
            required
            placeholder="Contoh: Bisnis 10, Bisma MAN 2 Ponorogo"
        >
        <div class="invalid-feedback d-none" id="name-error"></div>
    </div>

    <div class="mb-3">
        <label for="package_code" class="form-label">Kode Paket <span class="text-danger">*</span></label>
        <input
            type="text"
            class="form-control"
            id="package_code"
            name="package_code"
            value="{{ $isEdit ? $package->package_code : old('package_code') }}"
            required
            placeholder="Contoh: 2403145894"
        >
        <small class="text-muted">Kode unik untuk paket (harus unique)</small>
        <div class="invalid-feedback d-none" id="package_code-error"></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="speed" class="form-label">Kecepatan</label>
                <input
                    type="text"
                    class="form-control"
                    id="speed"
                    name="speed"
                    value="{{ $isEdit ? $package->speed : old('speed') }}"
                    placeholder="Contoh: 100Mbps, 50Mbps"
                >
                <div class="invalid-feedback d-none" id="speed-error"></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <label for="service_type" class="form-label">Type Layanan <span class="text-danger">*</span></label>
                <select class="form-select" id="service_type" name="service_type" required>
                    <option value="">Pilih Type Layanan</option>
                    <option value="Dedicated Internet" {{ ($isEdit && $package->service_type == 'Dedicated Internet') || old('service_type') == 'Dedicated Internet' ? 'selected' : '' }}>Dedicated Internet</option>
                    <option value="Internet Broadband" {{ ($isEdit && $package->service_type == 'Internet Broadband') || old('service_type') == 'Internet Broadband' ? 'selected' : '' }}>Internet Broadband</option>
                    <option value="Wireless" {{ ($isEdit && $package->service_type == 'Wireless') || old('service_type') == 'Wireless' ? 'selected' : '' }}>Wireless</option>
                    <option value="Fiber" {{ ($isEdit && $package->service_type == 'Fiber') || old('service_type') == 'Fiber' ? 'selected' : '' }}>Fiber</option>
                </select>
                <div class="invalid-feedback d-none" id="service_type-error"></div>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="price" class="form-label">Harga (Rp) <span class="text-danger">*</span></label>
        <input
            type="number"
            class="form-control"
            id="price"
            name="price"
            value="{{ $isEdit ? $package->price : old('price') }}"
            required
            min="0"
            step="0.01"
            placeholder="0"
        >
        <div class="invalid-feedback d-none" id="price-error"></div>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Deskripsi</label>
        <textarea
            class="form-control"
            id="description"
            name="description"
            rows="3"
            placeholder="Deskripsi paket..."
        >{{ $isEdit ? $package->description : old('description') }}</textarea>
        <div class="invalid-feedback d-none" id="description-error"></div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="mb-3">
                <label for="sort_order" class="form-label">Urutan Tampil</label>
                <input
                    type="number"
                    class="form-control"
                    id="sort_order"
                    name="sort_order"
                    value="{{ $isEdit ? $package->sort_order : (old('sort_order') ?? 0) }}"
                    min="0"
                    placeholder="0"
                >
                <small class="text-muted">Urutan tampil di dropdown (0 = pertama)</small>
                <div class="invalid-feedback d-none" id="sort_order-error"></div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-3">
                <div class="form-check mt-4">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="active"
                        name="active"
                        value="1"
                        {{ ($isEdit && $package->active) || (!isset($package) && old('active', true)) ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="active">
                        Aktif
                    </label>
                </div>
            </div>
        </div>
    </div>
</form>

