<div class="row">
    <div class="col-md-6">
        <table class="table table-bordered">
            <tr>
                <th width="40%">Nama Paket</th>
                <td>{{ $package->name }}</td>
            </tr>
            <tr>
                <th>Kode Paket</th>
                <td>{{ $package->package_code }}</td>
            </tr>
            <tr>
                <th>Kecepatan</th>
                <td>{{ $package->speed ?? '-' }}</td>
            </tr>
            <tr>
                <th>Type Layanan</th>
                <td>{{ $package->service_type }}</td>
            </tr>
            <tr>
                <th>Harga</th>
                <td><strong>Rp {{ number_format($package->price, 0, ',', '.') }}</strong></td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    @if($package->active)
                        <span class="badge bg-success">Aktif</span>
                    @else
                        <span class="badge bg-secondary">Tidak Aktif</span>
                    @endif
                </td>
            </tr>
            <tr>
                <th>Urutan Tampil</th>
                <td>{{ $package->sort_order }}</td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        @if($package->description)
        <div class="mb-3">
            <h6>Deskripsi</h6>
            <p>{{ $package->description }}</p>
        </div>
        @endif

        <div class="mb-3">
            <h6>Pelanggan yang Menggunakan</h6>
            <p class="text-muted">{{ $package->customers->count() }} pelanggan</p>
        </div>

        <div class="mb-3">
            <h6>Dibuat</h6>
            <p class="text-muted">{{ $package->created_at->format('d/m/Y H:i') }}</p>
        </div>

        @if($package->updated_at != $package->created_at)
        <div class="mb-3">
            <h6>Diperbarui</h6>
            <p class="text-muted">{{ $package->updated_at->format('d/m/Y H:i') }}</p>
        </div>
        @endif
    </div>
</div>

