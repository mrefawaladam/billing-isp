<?php

namespace App\Exports;

use App\Models\Customer;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class CustomerExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $customers;

    public function __construct($customers = null)
    {
        $this->customers = $customers ?? Customer::with(['package', 'assignedUsers'])->get();
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->customers;
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Kode Pelanggan',
            'Nama',
            'No. Telepon',
            'Alamat',
            'Kabupaten',
            'Kecamatan',
            'Kelurahan/Desa',
            'Latitude',
            'Longitude',
            'Tipe',
            'Status Aktif',
            'Kode Paket',
            'Nama Paket',
            'Biaya Bulanan',
            'Gunakan Harga Custom',
            'Diskon',
            'PPN Termasuk',
            'Total Biaya',
            'Tanggal Jatuh Tempo',
            'Penanggung Jawab',
        ];
    }

    /**
     * @param mixed $customer
     * @return array
     */
    public function map($customer): array
    {
        return [
            $customer->customer_code ?? '',
            $customer->name ?? '',
            $customer->phone ?? '',
            $customer->address ?? '',
            $customer->kabupaten ?? '',
            $customer->kecamatan ?? '',
            $customer->kelurahan ?? '',
            $customer->lat ?? '',
            $customer->lng ?? '',
            $customer->type ?? '',
            $customer->active ? 'Aktif' : 'Tidak Aktif',
            $customer->package ? $customer->package->package_code : '',
            $customer->package ? $customer->package->name : '',
            $customer->monthly_fee ?? 0,
            $customer->use_custom_price ? 'Ya' : 'Tidak',
            $customer->discount ?? 0,
            $customer->ppn_included ? 'Ya' : 'Tidak',
            $customer->total_fee ?? 0,
            $customer->invoice_due_day ?? '',
            $customer->assignedUsers->pluck('name')->join(', ') ?? '',
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row (headings)
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15,  // Kode Pelanggan
            'B' => 25,  // Nama
            'C' => 15,  // No. Telepon
            'D' => 40,  // Alamat
            'E' => 20,  // Kabupaten
            'F' => 20,  // Kecamatan
            'G' => 20,  // Kelurahan/Desa
            'H' => 12,  // Latitude
            'I' => 12,  // Longitude
            'J' => 12,  // Tipe
            'K' => 15,  // Status Aktif
            'L' => 15,  // Kode Paket
            'M' => 25,  // Nama Paket
            'N' => 15,  // Biaya Bulanan
            'O' => 18,  // Gunakan Harga Custom
            'P' => 12,  // Diskon
            'Q' => 15,  // PPN Termasuk
            'R' => 15,  // Total Biaya
            'S' => 15,  // Tanggal Jatuh Tempo
            'T' => 25,  // Penanggung Jawab
        ];
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return 'Data Pelanggan';
    }
}

