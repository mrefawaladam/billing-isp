@php
    $customer = \App\Models\Customer::with('devices')->findOrFail($customerId);
@endphp

<div class="row">
    <div class="col-12">
        <div class="card border">
            <div class="card-header bg-light">
                <h6 class="mb-0"><i class="ti ti-device-desktop me-2"></i>Tambah Perangkat Pelanggan</h6>
                <small class="text-muted">Isi form di bawah untuk menambahkan perangkat baru</small>
            </div>

            <!-- Device Form -->
            <div class="card-body border-bottom" id="device-form-container">
                <form id="device-form">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="device_id" id="device_id" value="">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Nama Perangkat <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="name" id="device-name" required placeholder="Contoh: Router Utama">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">MAC Address</label>
                            <input type="text" class="form-control" name="mac_address" id="device-mac" placeholder="00:11:22:33:44:55">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Foto Perangkat</label>
                            <input type="file" class="form-control" name="device_photo" id="device-photo" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF (Max: 5MB)</small>
                            <div id="device-photo-preview" class="mt-2" style="display: none;">
                                <img id="device-photo-img" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Foto Lokasi</label>
                            <input type="file" class="form-control" name="location_photo" id="location-photo" accept="image/*">
                            <small class="text-muted">Format: JPG, PNG, GIF (Max: 5MB)</small>
                            <div id="location-photo-preview" class="mt-2" style="display: none;">
                                <img id="location-photo-img" class="img-thumbnail" style="max-width: 150px; max-height: 150px;">
                            </div>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea class="form-control" name="note" id="device-note" rows="2" placeholder="Catatan tambahan tentang perangkat"></textarea>
                        </div>
                        <div class="col-12">
                            <button type="button" class="btn btn-primary btn-sm" id="btn-submit-device-form">
                                <i class="ti ti-check me-1"></i> Simpan Perangkat
                            </button>
                            <button type="button" class="btn btn-secondary btn-sm" id="btn-reset-device-form">
                                <i class="ti ti-refresh me-1"></i> Reset Form
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Devices List -->
            <div class="card-body" id="devices-list">
                @if($customer->devices && $customer->devices->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Perangkat</th>
                                    <th>MAC Address</th>
                                    <th>Foto Perangkat</th>
                                    <th>Foto Lokasi</th>
                                    <th>Catatan</th>
                                    <th width="100">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($customer->devices as $device)
                                    <tr class="device-item" data-device-id="{{ $device->id }}">
                                        <td><strong>{{ $device->name }}</strong></td>
                                        <td><code>{{ $device->mac_address ?? '-' }}</code></td>
                                        <td>
                                            @if($device->device_photo_url)
                                                <a href="{{ $device->device_photo_url }}" target="_blank">
                                                    <img src="{{ $device->device_photo_url }}" alt="Foto Perangkat" class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($device->location_photo_url)
                                                <a href="{{ $device->location_photo_url }}" target="_blank">
                                                    <img src="{{ $device->location_photo_url }}" alt="Foto Lokasi" class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                                                </a>
                                            @else
                                                <span class="text-muted small">-</span>
                                            @endif
                                        </td>
                                        <td><small>{{ $device->note ?? '-' }}</small></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-warning btn-edit-device" data-device-id="{{ $device->id }}" title="Edit">
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-danger btn-delete-device" data-device-id="{{ $device->id }}" title="Hapus">
                                                <i class="ti ti-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="ti ti-device-off fs-1 text-muted"></i>
                        <p class="text-muted mt-2 mb-0">Belum ada perangkat yang ditambahkan.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

