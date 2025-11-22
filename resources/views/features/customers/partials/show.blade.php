<div class="customer-details">
    <!-- Informasi Dasar -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0"><i class="ti ti-user me-2"></i>Informasi Dasar</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ti ti-id-badge text-primary me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Kode Pelanggan</small>
                            <strong>{{ $customer->customer_code ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ti ti-user text-primary me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Nama Lengkap</small>
                            <strong>{{ $customer->name }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ti ti-phone text-primary me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Nomor WhatsApp</small>
                            <strong>{{ $customer->phone ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ti ti-tag text-primary me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Jenis Pelanggan</small>
                            @php
                                $badges = [
                                    'rumahan' => 'bg-primary',
                                    'kantor' => 'bg-success',
                                    'sekolah' => 'bg-info',
                                    'free' => 'bg-secondary',
                                ];
                                $badge = $badges[$customer->type] ?? 'bg-secondary';
                            @endphp
                            <span class="badge {{ $badge }}">{{ ucfirst($customer->type) }}</span>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ti ti-map-pin text-primary me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Alamat Lengkap</small>
                            <strong>{{ $customer->address ?? '-' }}</strong>
                        </div>
                    </div>
                </div>
                @if($customer->lat && $customer->lng)
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ti ti-map text-primary me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Koordinat</small>
                            <strong>{{ $customer->lat }}, {{ $customer->lng }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <a href="https://www.google.com/maps?q={{ $customer->lat }},{{ $customer->lng }}" target="_blank" class="btn btn-sm btn-primary mt-4">
                        <i class="ti ti-map-pin me-1"></i> Lihat di Google Maps
                    </a>
                </div>
                @endif
                @if($customer->house_photo_url)
                <div class="col-12">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ti ti-photo text-primary me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block mb-2">Foto Rumah</small>
                            <img src="{{ $customer->house_photo_url }}" alt="Foto Rumah" class="img-thumbnail" style="max-width: 400px; max-height: 400px;">
                        </div>
                    </div>
                </div>
                @endif
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ti ti-user-check text-primary me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Penanggung Jawab</small>
                            <strong>{{ $customer->assignedUser ? $customer->assignedUser->name : '-' }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-start">
                        <div class="flex-shrink-0">
                            <i class="ti ti-circle-check text-primary me-2"></i>
                        </div>
                        <div class="flex-grow-1">
                            <small class="text-muted d-block">Status</small>
                            <span class="badge {{ $customer->active ? 'bg-success' : 'bg-danger' }}">
                                {{ $customer->active ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Informasi Paket & Biaya -->
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header bg-success text-white">
            <h6 class="mb-0"><i class="ti ti-wallet me-2"></i>Informasi Paket & Biaya</h6>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted d-block mb-1">Biaya Bulanan</small>
                        <h5 class="mb-0 text-primary">Rp {{ number_format($customer->monthly_fee, 0, ',', '.') }}</h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded">
                        <small class="text-muted d-block mb-1">Diskon</small>
                        <h5 class="mb-0 text-warning">Rp {{ number_format($customer->discount, 0, ',', '.') }}</h5>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="p-3 bg-primary text-white rounded">
                        <small class="d-block mb-1 opacity-75">Total Biaya</small>
                        <h5 class="mb-0 fw-bold">Rp {{ number_format($customer->total_fee, 0, ',', '.') }}</h5>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-receipt text-success me-2"></i>
                        <div>
                            <small class="text-muted d-block">PPN</small>
                            <strong>{{ $customer->ppn_included ? 'Sudah Termasuk' : 'Belum Termasuk (10%)' }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-calendar text-success me-2"></i>
                        <div>
                            <small class="text-muted d-block">Tanggal Jatuh Tempo</small>
                            <strong>Tanggal {{ $customer->invoice_due_day }} setiap bulan</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Perangkat -->
    @if($customer->devices->count() > 0)
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-header bg-info text-white">
            <h6 class="mb-0"><i class="ti ti-device-desktop me-2"></i>Perangkat ({{ $customer->devices->count() }})</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama Perangkat</th>
                            <th>MAC Address</th>
                            <th>Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($customer->devices as $device)
                        <tr>
                            <td>
                                <i class="ti ti-router me-1"></i>{{ $device->name }}
                                @if($device->device_photo_url)
                                    <br><a href="{{ $device->device_photo_url }}" target="_blank" class="text-primary small">
                                        <i class="ti ti-photo me-1"></i>Lihat Foto Perangkat
                                    </a>
                                @endif
                            </td>
                            <td><code>{{ $device->mac_address ?? '-' }}</code></td>
                            <td>
                                {{ $device->note ?? '-' }}
                                @if($device->location_photo_url)
                                    <br><a href="{{ $device->location_photo_url }}" target="_blank" class="text-info small">
                                        <i class="ti ti-map-pin me-1"></i>Lihat Foto Lokasi
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Metadata -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-clock text-muted me-2"></i>
                        <div>
                            <small class="text-muted d-block">Dibuat</small>
                            <strong>{{ $customer->created_at->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="d-flex align-items-center">
                        <i class="ti ti-edit text-muted me-2"></i>
                        <div>
                            <small class="text-muted d-block">Diperbarui</small>
                            <strong>{{ $customer->updated_at->format('d/m/Y H:i') }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

