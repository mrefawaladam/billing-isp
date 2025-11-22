<div class="alert alert-warning mb-4">
    <i class="ti ti-alert-triangle me-2"></i>
    <strong>Konfirmasi Pembayaran</strong>
    <p class="mb-0 mt-2">Apakah Anda yakin ingin menandai tagihan ini sebagai sudah dibayar? Pastikan pembayaran sudah diterima sebelum mengkonfirmasi.</p>
</div>

<div class="row">
    <div class="col-md-6">
        <h5 class="mb-3">Informasi Tagihan</h5>
        <table class="table table-bordered">
            <tr>
                <th width="40%">No. Tagihan</th>
                <td>{{ $invoice->invoice_number ?? '-' }}</td>
            </tr>
            <tr>
                <th>Periode</th>
                <td>{{ \Carbon\Carbon::create()->month($invoice->month)->locale('id')->monthName }} {{ $invoice->year }}</td>
            </tr>
            <tr>
                <th>Jatuh Tempo</th>
                <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    @if($invoice->status === 'OVERDUE')
                        <span class="badge bg-danger">Terlambat
                            @if($invoice->months_overdue > 0)
                                ({{ $invoice->months_overdue }} bulan)
                            @endif
                        </span>
                    @else
                        <span class="badge bg-warning">Belum Dibayar</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="col-md-6">
        <h5 class="mb-3">Informasi Pelanggan</h5>
        <table class="table table-bordered">
            <tr>
                <th width="40%">Kode Pelanggan</th>
                <td>{{ $invoice->customer->customer_code ?? '-' }}</td>
            </tr>
            <tr>
                <th>Nama</th>
                <td>{{ $invoice->customer->name ?? '-' }}</td>
            </tr>
            <tr>
                <th>Telepon</th>
                <td>{{ $invoice->customer->phone ?? '-' }}</td>
            </tr>
            <tr>
                <th>Alamat</th>
                <td>{{ $invoice->customer->address ?? '-' }}</td>
            </tr>
        </table>
    </div>
</div>

<div class="row mt-3">
    <div class="col-12">
        <h5 class="mb-3">Rincian Tagihan</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Keterangan</th>
                    <th class="text-end">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Biaya Bulanan</td>
                    <td class="text-end">Rp {{ number_format($invoice->customer->monthly_fee ?? 0, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->discount_amount > 0)
                <tr>
                    <td>Diskon</td>
                    <td class="text-end text-danger">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td>Subtotal (Sebelum PPN)</td>
                    <td class="text-end">Rp {{ number_format($invoice->amount_before_tax, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->tax_amount > 0)
                <tr>
                    <td>PPN (10%)</td>
                    <td class="text-end">Rp {{ number_format($invoice->tax_amount, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr class="table-primary">
                    <th>Total Tagihan</th>
                    <th class="text-end">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</th>
                </tr>
            </tbody>
        </table>
    </div>
</div>

