@php
    $isEdit = isset($customer) && $customer !== null;
@endphp

<form id="customer-form" action="{{ $formAction }}" method="POST" enctype="multipart/form-data" @if($isEdit) data-customer-id="{{ $customer->id }}" @endif>
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        <!-- Informasi Dasar -->
        <div class="col-md-6">
            <h5 class="mb-3">Informasi Dasar</h5>

            <div class="mb-3">
                <label for="customer_code" class="form-label">Kode Pelanggan</label>
                <input
                    type="text"
                    class="form-control"
                    id="customer_code"
                    name="customer_code"
                    value="{{ $isEdit ? $customer->customer_code : old('customer_code') }}"
                    placeholder="Akan di-generate otomatis jika kosong"
                >
                <div class="invalid-feedback d-none" id="customer_code-error"></div>
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                <input
                    type="text"
                    class="form-control"
                    id="name"
                    name="name"
                    value="{{ $isEdit ? $customer->name : old('name') }}"
                    required
                >
                <div class="invalid-feedback d-none" id="name-error"></div>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Nomor WhatsApp</label>
                <input
                    type="text"
                    class="form-control"
                    id="phone"
                    name="phone"
                    value="{{ $isEdit ? $customer->phone : old('phone') }}"
                    placeholder="081234567890"
                >
                <div class="invalid-feedback d-none" id="phone-error"></div>
            </div>

            <div class="mb-3">
                <label for="address" class="form-label">Alamat Lengkap</label>
                <textarea
                    class="form-control"
                    id="address"
                    name="address"
                    rows="3"
                >{{ $isEdit ? $customer->address : old('address') }}</textarea>
                <div class="invalid-feedback d-none" id="address-error"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">
                    <i class="ti ti-map-pin me-1"></i>Lokasi Detail
                </label>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <input
                            type="text"
                            class="form-control"
                            id="kabupaten"
                            name="kabupaten"
                            value="{{ $isEdit ? $customer->kabupaten : old('kabupaten') }}"
                            placeholder="Kabupaten"
                        >
                        <div class="invalid-feedback d-none" id="kabupaten-error"></div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input
                            type="text"
                            class="form-control"
                            id="kecamatan"
                            name="kecamatan"
                            value="{{ $isEdit ? $customer->kecamatan : old('kecamatan') }}"
                            placeholder="Kecamatan"
                        >
                        <div class="invalid-feedback d-none" id="kecamatan-error"></div>
                    </div>
                    <div class="col-md-4 mb-2">
                        <input
                            type="text"
                            class="form-control"
                            id="kelurahan"
                            name="kelurahan"
                            value="{{ $isEdit ? $customer->kelurahan : old('kelurahan') }}"
                            placeholder="Kelurahan/Desa"
                        >
                        <div class="invalid-feedback d-none" id="kelurahan-error"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="lat" class="form-label">Latitude</label>
                    <input
                        type="number"
                        step="any"
                        class="form-control"
                        id="lat"
                        name="lat"
                        value="{{ $isEdit ? $customer->lat : old('lat') }}"
                        placeholder="-6.2088"
                    >
                    <div class="invalid-feedback d-none" id="lat-error"></div>
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lng" class="form-label">Longitude</label>
                    <input
                        type="number"
                        step="any"
                        class="form-control"
                        id="lng"
                        name="lng"
                        value="{{ $isEdit ? $customer->lng : old('lng') }}"
                        placeholder="106.8456"
                    >
                    <div class="invalid-feedback d-none" id="lng-error"></div>
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label">Pilih Lokasi di Peta</label>
                <div style="position: relative;">
                    <div class="input-group mb-2">
                        <input
                            type="text"
                            class="form-control"
                            id="location-search"
                            placeholder="Cari lokasi (contoh: Jakarta, Bandung, Jl. Sudirman No. 1)"
                        >
                        <button class="btn btn-outline-primary" type="button" id="btn-search-location">
                            <i class="bi bi-search"></i> Cari
                        </button>
                    </div>
                    <div id="search-results" class="list-group" style="max-height: 150px; overflow-y: auto; display: none; position: absolute; z-index: 1000; width: 100%; top: 100%;"></div>
                </div>
                <div id="location-map" style="height: 300px; width: 100%; border-radius: 8px; border: 1px solid #ddd; margin-top: 10px;"></div>
                <small class="text-muted">Cari lokasi di atas, klik di peta, atau seret marker untuk memilih lokasi</small>
            </div>
        </div>

        <!-- Paket & Biaya -->
        <div class="col-md-6">
            <h5 class="mb-3">Paket & Biaya</h5>

            <div class="mb-3">
                <label for="type" class="form-label">Jenis Pelanggan <span class="text-danger">*</span></label>
                <select class="form-select" id="type" name="type" required>
                    <option value="">Pilih Jenis</option>
                    <option value="rumahan" {{ ($isEdit && $customer->type === 'rumahan') || old('type') === 'rumahan' ? 'selected' : '' }}>Rumahan</option>
                    <option value="kantor" {{ ($isEdit && $customer->type === 'kantor') || old('type') === 'kantor' ? 'selected' : '' }}>Kantor</option>
                    <option value="sekolah" {{ ($isEdit && $customer->type === 'sekolah') || old('type') === 'sekolah' ? 'selected' : '' }}>Sekolah</option>
                    <option value="free" {{ ($isEdit && $customer->type === 'free') || old('type') === 'free' ? 'selected' : '' }}>Free</option>
                </select>
                <div class="invalid-feedback d-none" id="type-error"></div>
            </div>

            <div class="mb-3">
                <label for="assigned_users" class="form-label">Penanggung Jawab</label>
                <select class="form-select" id="assigned_users" name="assigned_users[]" multiple size="5">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" 
                            {{ ($isEdit && $customer->assignedUsers->contains($user->id)) || (is_array(old('assigned_users')) && in_array($user->id, old('assigned_users'))) ? 'selected' : '' }}>
                            {{ $user->name }}
                        </option>
                    @endforeach
                </select>
                <small class="text-muted">
                    <i class="ti ti-info-circle me-1"></i>
                    Gunakan Ctrl (Windows/Linux) atau Cmd (Mac) untuk memilih multiple
                </small>
                <div class="invalid-feedback d-none" id="assigned_users-error"></div>
            </div>

            <div class="mb-3">
                <label class="form-label">Paket Internet</label>
                <div class="input-group mb-2">
                    <input
                        type="text"
                        class="form-control"
                        id="package-display"
                        readonly
                        placeholder="Pilih paket atau input manual"
                        value="{{ $isEdit && $customer->package ? $customer->package->name . ' - Rp ' . number_format($customer->package->price, 0, ',', '.') : '' }}"
                        style="background-color: #f8f9fa;"
                    >
                    <button type="button" class="btn btn-primary" id="btn-select-package">
                        <i class="ti ti-package me-1"></i> Pilih Paket
                    </button>
                </div>
                <input type="hidden" id="package_id" name="package_id" value="{{ $isEdit ? $customer->package_id : old('package_id') }}">
                
                <div class="form-check mt-2">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="use_custom_price"
                        name="use_custom_price"
                        value="1"
                        {{ ($isEdit && $customer->use_custom_price) || old('use_custom_price') ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="use_custom_price">
                        Gunakan Harga Custom
                    </label>
                </div>
                <small class="text-muted">
                    <i class="ti ti-info-circle me-1"></i>
                    Jika tidak memilih paket, Anda dapat menginput biaya bulanan secara manual
                </small>
            </div>

            <div class="mb-3" id="monthly-fee-wrapper">
                <label for="monthly_fee" class="form-label">Biaya Bulanan (Rp) <span class="text-danger">*</span></label>
                <input
                    type="number"
                    step="0.01"
                    class="form-control"
                    id="monthly_fee"
                    name="monthly_fee"
                    value="{{ $isEdit ? $customer->monthly_fee : old('monthly_fee') }}"
                    required
                    min="0"
                >
                <div class="invalid-feedback d-none" id="monthly_fee-error"></div>
            </div>

            <div class="mb-3">
                <label for="discount" class="form-label">Diskon (Rp)</label>
                <input
                    type="number"
                    step="0.01"
                    class="form-control"
                    id="discount"
                    name="discount"
                    value="{{ $isEdit ? $customer->discount : old('discount') }}"
                    min="0"
                >
                <div class="invalid-feedback d-none" id="discount-error"></div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="ppn_included"
                        name="ppn_included"
                        value="1"
                        {{ ($isEdit && $customer->ppn_included) || old('ppn_included') ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="ppn_included">
                        PPN Sudah Termasuk
                    </label>
                </div>
                <small class="text-muted">Centang jika PPN sudah termasuk dalam biaya bulanan</small>
            </div>

            <div class="mb-3">
                <label for="total_fee" class="form-label">Total Biaya Bulanan (Rp)</label>
                <input
                    type="text"
                    class="form-control bg-light"
                    id="total_fee"
                    readonly
                    value="{{ $isEdit ? number_format($customer->total_fee, 0, ',', '.') : '0' }}"
                >
                <small class="text-muted">Dihitung otomatis: (Biaya Bulanan - Diskon) + PPN 10%</small>
            </div>

            <div class="mb-3">
                <label for="invoice_due_day" class="form-label">Tanggal Jatuh Tempo <span class="text-danger">*</span></label>
                <input
                    type="number"
                    class="form-control"
                    id="invoice_due_day"
                    name="invoice_due_day"
                    value="{{ $isEdit ? $customer->invoice_due_day : old('invoice_due_day', 1) }}"
                    required
                    min="1"
                    max="31"
                >
                <small class="text-muted">Hari setiap bulan (1-31)</small>
                <div class="invalid-feedback d-none" id="invoice_due_day-error"></div>
            </div>

            <div class="mb-3">
                <div class="form-check">
                    <input
                        class="form-check-input"
                        type="checkbox"
                        id="active"
                        name="active"
                        value="1"
                        {{ ($isEdit && $customer->active) || (!isset($customer) && old('active', true)) ? 'checked' : '' }}
                    >
                    <label class="form-check-label" for="active">
                        Aktif
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Foto Rumah & Identitas Pelanggan -->
    <div class="row mt-4">
        <div class="col-12">
            <h5 class="mb-3 border-bottom pb-2">
                <i class="ti ti-photo me-2"></i>Dokumentasi Pelanggan
            </h5>
        </div>
        <div class="col-md-6">
            <div class="card border">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="ti ti-home me-2 text-primary"></i>Foto Rumah Pelanggan
                    </h6>
                    <div class="mb-3">
                        <label for="house_photo" class="form-label">Upload Foto Rumah</label>
                        <input
                            type="file"
                            class="form-control"
                            id="house_photo"
                            name="house_photo"
                            accept="image/*"
                        >
                        <small class="text-muted">
                            <i class="ti ti-info-circle me-1"></i>
                            Format: JPG, PNG, GIF (Max: 5MB)
                        </small>
                        @if($isEdit && $customer->house_photo_url)
                            <div class="mt-3">
                                <p class="mb-1 small text-muted">Foto Saat Ini:</p>
                                <img src="{{ $customer->house_photo_url }}" alt="Foto Rumah" class="img-thumbnail" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border">
                <div class="card-body">
                    <h6 class="card-title mb-3">
                        <i class="ti ti-id me-2 text-success"></i>Foto Identitas Pelanggan
                    </h6>
                    <div class="mb-3">
                        <label for="identity_photo" class="form-label">Upload Foto Identitas (KTP/SIM/dll)</label>
                        <input
                            type="file"
                            class="form-control"
                            id="identity_photo"
                            name="identity_photo"
                            accept="image/*"
                        >
                        <small class="text-muted">
                            <i class="ti ti-info-circle me-1"></i>
                            Format: JPG, PNG, GIF (Max: 5MB)
                        </small>
                        @if($isEdit && $customer->identity_photo_url)
                            <div class="mt-3">
                                <p class="mb-1 small text-muted">Foto Saat Ini:</p>
                                <img src="{{ $customer->identity_photo_url }}" alt="Foto Identitas" class="img-thumbnail" style="max-width: 100%; max-height: 300px; border-radius: 8px;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>


    <script>
    // Calculate total fee automatically
    function calculateTotalFee() {
        const monthlyFee = parseFloat($('#monthly_fee').val()) || 0;
        const discount = parseFloat($('#discount').val()) || 0;
        const ppnIncluded = $('#ppn_included').is(':checked');

        let amountBeforeTax = monthlyFee - discount;
        let totalFee = amountBeforeTax;

        if (!ppnIncluded) {
            const taxAmount = amountBeforeTax * 0.1;
            totalFee = amountBeforeTax + taxAmount;
        }

        $('#total_fee').val(totalFee.toLocaleString('id-ID'));
    }

    $(document).ready(function() {
        $('#monthly_fee, #discount, #ppn_included').on('input change', calculateTotalFee);
        
        // Package selection handlers are now in customers/index.blade.php
        // to avoid nested modal issues
    });

    </script>
</form>


