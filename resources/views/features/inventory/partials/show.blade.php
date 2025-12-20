<div class="row">
    <div class="col-md-6">
        <h5 class="mb-3">Informasi Item</h5>
        <table class="table table-bordered">
            <tr>
                <th width="40%">Kode</th>
                <td>{{ $inventory->code ?? '-' }}</td>
            </tr>
            <tr>
                <th>Nama</th>
                <td>{{ $inventory->name }}</td>
            </tr>
            <tr>
                <th>Jenis</th>
                <td>{{ ucfirst($inventory->type) }}</td>
            </tr>
            <tr>
                <th>Brand</th>
                <td>{{ $inventory->brand ?? '-' }}</td>
            </tr>
            <tr>
                <th>Model</th>
                <td>{{ $inventory->model ?? '-' }}</td>
            </tr>
            <tr>
                <th>Deskripsi</th>
                <td>{{ $inventory->description ?? '-' }}</td>
            </tr>
            <tr>
                <th>Stok</th>
                <td>
                    <strong>{{ $inventory->stock_quantity }}</strong> {{ $inventory->unit }}
                    @if($inventory->isLowStock())
                        <span class="badge bg-warning ms-2">Stok Menipis</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Stok Minimum</th>
                <td>{{ $inventory->min_stock }} {{ $inventory->unit }}</td>
            </tr>
            <tr>
                <th>Harga</th>
                <td>Rp {{ number_format($inventory->price, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Lokasi</th>
                <td>{{ $inventory->location ?? '-' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    @if($inventory->active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Tidak Aktif</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h5 class="mb-3">Riwayat Penggunaan</h5>
        <div style="max-height: 400px; overflow-y: auto;">
            @if($usageHistory->count() > 0)
                <div class="list-group">
                    @foreach($usageHistory as $usage)
                        <div class="list-group-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        {{ $usage->usage_type == 'installed' ? 'Dipasang' : 
                                           ($usage->usage_type == 'returned' ? 'Dikembalikan' : 
                                           ($usage->usage_type == 'maintenance' ? 'Maintenance' : 
                                           ($usage->usage_type == 'damaged' ? 'Rusak' : 
                                           ($usage->usage_type == 'lost' ? 'Hilang' : 
                                           ($usage->usage_type == 'restock' ? 'Restock' : ucfirst($usage->usage_type)))))) }}
                                    </h6>
                                    <p class="mb-1">
                                        <strong>Jumlah:</strong> {{ $usage->quantity }} {{ $inventory->unit }}
                                    </p>
                                    @if($usage->customer)
                                        <p class="mb-1">
                                            <strong>Pelanggan:</strong> {{ $usage->customer->name }}
                                        </p>
                                    @endif
                                    @if($usage->device)
                                        <p class="mb-1">
                                            <strong>Perangkat:</strong> {{ $usage->device->name }}
                                        </p>
                                    @endif
                                    @if($usage->notes)
                                        <p class="mb-1 text-muted">
                                            <small>{{ $usage->notes }}</small>
                                        </p>
                                    @endif
                                    <small class="text-muted">
                                        {{ $usage->used_at->format('d/m/Y H:i') }}
                                        @if($usage->usedBy)
                                            oleh {{ $usage->usedBy->name }}
                                        @endif
                                    </small>
                                </div>
                                <span class="badge 
                                    {{ $usage->usage_type == 'installed' ? 'bg-primary' : 
                                       ($usage->usage_type == 'returned' ? 'bg-success' : 
                                       ($usage->usage_type == 'restock' ? 'bg-info' : 'bg-secondary')) }}">
                                    {{ ucfirst($usage->usage_type) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted">Belum ada riwayat penggunaan</p>
            @endif
        </div>
    </div>
</div>

