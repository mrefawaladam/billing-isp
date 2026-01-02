<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Font;

class CustomerTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize, WithEvents
{
    /**
     * @return array
     */
    public function array(): array
    {
        // Sample data untuk template
        return [
            [
                'CUST001',
                'John Doe',
                '081234567890',
                'Jl. Contoh No. 123',
                'Ponorogo',
                'Babadan',
                'Kertosari',
                '-7.8686',
                '111.4620',
                'Residential',
                'Aktif',
                'PKG001',
                'Paket 50 Mbps',
                '150000',
                'Tidak',
                '0',
                'Ya',
                '165000',
                '10',
                'Admin User',
            ],
            [
                'CUST002',
                'Jane Smith',
                '081987654321',
                'Jl. Sample No. 456',
                'Ponorogo',
                'Ponorogo',
                'Setono',
                '-7.8700',
                '111.4600',
                'Business',
                'Aktif',
                'PKG002',
                'Paket 100 Mbps',
                '250000',
                'Tidak',
                '5000',
                'Ya',
                '269500',
                '15',
                'Staff User',
            ],
        ];
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
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Get current highest row (where data ends)
                $highestRow = $sheet->getHighestRow();
                
                // Move existing data down (headings at row 1, data at row 2+)
                // We'll move everything down by 22 rows
                $sheet->insertNewRowBefore(1, 22);
                
                // Now headings are at row 23, data starts at row 24
                
                // Add instructions at the top
                $sheet->setCellValue('A1', 'PANDUAN IMPORT DATA PELANGAN');
                $sheet->mergeCells('A1:T1');
                $sheet->getStyle('A1')->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'size' => 14,
                        'color' => ['rgb' => 'FFFFFF'],
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E75B6'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                ]);
                
                // Instructions content
                $instructions = [
                    ['Kolom', 'Wajib/Kosong', 'Format & Validasi'],
                    ['Kode Pelanggan', 'Boleh Kosong', 'Jika kosong, akan dibuat otomatis. Jika diisi, harus unik (tidak boleh duplikat).'],
                    ['Nama', 'WAJIB', 'Teks, maksimal 255 karakter.'],
                    ['No. Telepon', 'Boleh Kosong', 'Format: 081234567890 atau 6281234567890.'],
                    ['Alamat', 'Boleh Kosong', 'Teks bebas.'],
                    ['Kabupaten', 'Boleh Kosong', 'Teks bebas.'],
                    ['Kecamatan', 'Boleh Kosong', 'Teks bebas.'],
                    ['Kelurahan/Desa', 'Boleh Kosong', 'Teks bebas.'],
                    ['Latitude', 'Boleh Kosong', 'Angka desimal, contoh: -7.8686'],
                    ['Longitude', 'Boleh Kosong', 'Angka desimal, contoh: 111.4620'],
                    ['Tipe', 'Boleh Kosong', 'Pilihan: Residential, Business, School, atau Free. Default: Residential'],
                    ['Status Aktif', 'Boleh Kosong', 'Pilihan: Aktif atau Tidak Aktif. Default: Aktif'],
                    ['Kode Paket', 'Boleh Kosong', 'Harus sesuai dengan kode paket yang ada di sistem. Contoh: PKG001'],
                    ['Nama Paket', 'Boleh Kosong', 'Informasi saja, tidak digunakan untuk validasi.'],
                    ['Biaya Bulanan', 'Boleh Kosong', 'Angka tanpa titik/koma. Contoh: 150000. Jika kosong dan ada Kode Paket, akan menggunakan harga paket.'],
                    ['Gunakan Harga Custom', 'Boleh Kosong', 'Pilihan: Ya atau Tidak. Default: Tidak'],
                    ['Diskon', 'Boleh Kosong', 'Angka tanpa titik/koma. Contoh: 5000. Default: 0'],
                    ['PPN Termasuk', 'Boleh Kosong', 'Pilihan: Ya atau Tidak. Default: Ya'],
                    ['Total Biaya', 'Boleh Kosong', 'Angka tanpa titik/koma. Jika kosong, akan dihitung otomatis.'],
                    ['Tanggal Jatuh Tempo', 'Boleh Kosong', 'Angka 1-31 (tanggal dalam bulan). Contoh: 10'],
                    ['Penanggung Jawab', 'Boleh Kosong', 'Nama user yang ada di sistem, pisahkan dengan koma jika lebih dari satu. Contoh: Admin User, Staff User'],
                ];
                
                // Write instructions
                $row = 2;
                foreach ($instructions as $instruction) {
                    $sheet->setCellValue('A' . $row, $instruction[0]);
                    $sheet->setCellValue('B' . $row, $instruction[1]);
                    $sheet->setCellValue('C' . $row, $instruction[2]);
                    
                    // Style for header row
                    if ($row == 2) {
                        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                            'font' => ['bold' => true, 'size' => 11],
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'D9E1F2'],
                            ],
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => '000000'],
                                ],
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);
                    } else {
                        // Style for data rows
                        $sheet->getStyle('A' . $row . ':C' . $row)->applyFromArray([
                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' => Border::BORDER_THIN,
                                    'color' => ['rgb' => 'CCCCCC'],
                                ],
                            ],
                            'alignment' => [
                                'vertical' => Alignment::VERTICAL_TOP,
                                'wrapText' => true,
                            ],
                        ]);
                        
                        // Highlight required fields
                        if ($instruction[1] === 'WAJIB') {
                            $sheet->getStyle('A' . $row . ':B' . $row)->applyFromArray([
                                'fill' => [
                                    'fillType' => Fill::FILL_SOLID,
                                    'startColor' => ['rgb' => 'FFE699'],
                                ],
                                'font' => ['bold' => true],
                            ]);
                        }
                    }
                    $row++;
                }
                
                // Set column widths for instruction columns
                $sheet->getColumnDimension('A')->setWidth(25);
                $sheet->getColumnDimension('B')->setWidth(15);
                $sheet->getColumnDimension('C')->setWidth(80);
                
                // Freeze panes at row 23 (where data headers start after insert)
                $sheet->freezePane('A23');
            },
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Style the header row (will be at row 23 after insert)
            23 => [
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
            // Style sample data rows (rows 24-25 after insert)
            'A24:T25' => [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'F2F2F2'],
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
        return 'Template Import Pelanggan';
    }
}

