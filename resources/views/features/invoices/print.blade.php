<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tagihan {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header-logo {
            margin-bottom: 15px;
        }
        .header-logo img {
            max-height: 60px;
            width: auto;
        }
        .header h1 {
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header p {
            font-size: 12px;
        }
        .invoice-info {
            margin-bottom: 30px;
        }
        .invoice-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .invoice-info table td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .invoice-info table td:first-child {
            font-weight: bold;
            width: 30%;
            background-color: #f5f5f5;
        }
        .details {
            margin-bottom: 30px;
        }
        .details table {
            width: 100%;
            border-collapse: collapse;
        }
        .details table th,
        .details table td {
            padding: 8px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .details table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .details table td.text-end {
            text-align: right;
        }
        .total {
            margin-top: 20px;
        }
        .total table {
            width: 100%;
            border-collapse: collapse;
        }
        .total table td {
            padding: 8px;
            border: 1px solid #ddd;
        }
        .total table td:first-child {
            font-weight: bold;
            width: 80%;
        }
        .total table td:last-child {
            text-align: right;
            font-weight: bold;
            font-size: 14px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        @media print {
            body {
                padding: 0;
            }
            .no-print {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-logo">
            <img src="{{ asset('logo.png') }}" alt="Logo" onerror="this.style.display='none';">
        </div>
        <h1>TAGIHAN BULANAN</h1>
        <p>Internet Service Provider</p>
    </div>

    <div class="invoice-info">
        <table>
            <tr>
                <td>No. Tagihan</td>
                <td>{{ $invoice->invoice_number ?? '-' }}</td>
            </tr>
            <tr>
                <td>Periode</td>
                <td>{{ \Carbon\Carbon::create()->month($invoice->month)->locale('id')->monthName }} {{ $invoice->year }}</td>
            </tr>
            <tr>
                <td>Tanggal Generate</td>
                <td>{{ $invoice->generated_at ? $invoice->generated_at->format('d/m/Y H:i') : '-' }}</td>
            </tr>
            <tr>
                <td>Jatuh Tempo</td>
                <td>{{ $invoice->due_date ? $invoice->due_date->format('d/m/Y') : '-' }}</td>
            </tr>
            <tr>
                <td>Status</td>
                <td>
                    @if($invoice->status === 'PAID')
                        <strong>SUDAH DIBAYAR</strong>
                    @elseif($invoice->status === 'OVERDUE')
                        <strong style="color: red;">TERLAMBAT</strong>
                        @if($invoice->months_overdue > 0)
                            ({{ $invoice->months_overdue }} bulan)
                        @endif
                    @else
                        <strong>BELUM DIBAYAR</strong>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <div class="details">
        <h3 style="margin-bottom: 10px;">Informasi Pelanggan</h3>
        <table>
            <tr>
                <td>Kode Pelanggan</td>
                <td>{{ $invoice->customer->customer_code ?? '-' }}</td>
            </tr>
            <tr>
                <td>Nama</td>
                <td>{{ $invoice->customer->name ?? '-' }}</td>
            </tr>
            <tr>
                <td>Telepon</td>
                <td>{{ $invoice->customer->phone ?? '-' }}</td>
            </tr>
            <tr>
                <td>Alamat</td>
                <td>{{ $invoice->customer->address ?? '-' }}</td>
            </tr>
        </table>
    </div>

    <div class="details">
        <h3 style="margin-bottom: 10px;">Rincian Tagihan</h3>
        <table>
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
                    <td class="text-end">- Rp {{ number_format($invoice->discount_amount, 0, ',', '.') }}</td>
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
            </tbody>
        </table>
    </div>

    <div class="total">
        <table>
            <tr>
                <td>TOTAL TAGIHAN</td>
                <td>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <p>Terima kasih atas kepercayaan Anda menggunakan layanan kami.</p>
        <p>Dicetak pada: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="no-print" style="margin-top: 20px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">Cetak</button>
        <button onclick="window.close()" style="padding: 10px 20px; font-size: 14px; cursor: pointer; margin-left: 10px;">Tutup</button>
    </div>
</body>
</html>

