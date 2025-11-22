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
                <th>Tanggal Generate</th>
                <td>{{ $invoice->generated_at ? $invoice->generated_at->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            <tr>
                <th>Dibuat Oleh</th>
                <td>{{ $invoice->generatedBy ? $invoice->generatedBy->name : '-' }}</td>
            </tr>
            <tr>
                <th>Jatuh Tempo</th>
                <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>
                    @if($invoice->status === 'PAID')
                        <span class="badge bg-success">Sudah Dibayar</span>
                    @elseif($invoice->status === 'OVERDUE')
                        <span class="badge bg-danger">Terlambat</span>
                        @if($invoice->months_overdue > 0)
                            <span class="badge bg-danger ms-1">{{ $invoice->months_overdue }} bulan</span>
                        @endif
                    @else
                        <span class="badge bg-warning">Belum Dibayar</span>
                    @endif
                </td>
            </tr>
            @if($invoice->paid_at)
            <tr>
                <th>Tanggal Dibayar</th>
                <td>{{ $invoice->paid_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endif
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
            <tr>
                <th>Penanggung Jawab</th>
                <td>{{ $invoice->customer->assignedUser ? $invoice->customer->assignedUser->name : '-' }}</td>
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

@if($invoice->payments && $invoice->payments->count() > 0)
<div class="row mt-3">
    <div class="col-12">
        <h5 class="mb-3">Riwayat Pembayaran</h5>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Metode</th>
                    <th class="text-end">Jumlah</th>
                    <th>Diterima Oleh</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->payments as $payment)
                <tr>
                    <td>{{ $payment->paid_date ? \Carbon\Carbon::parse($payment->paid_date)->format('d/m/Y') : '-' }}</td>
                    <td>{{ strtoupper($payment->method ?? '-') }}</td>
                    <td class="text-end">Rp {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</td>
                    <td>
                        @php
                            $receivedBy = $payment->receivedBy ?? null;
                        @endphp
                        {{ $receivedBy ? $receivedBy->name : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

