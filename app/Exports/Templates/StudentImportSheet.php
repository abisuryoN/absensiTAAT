<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StudentImportSheet implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Template Import Siswa';
    }

    public function headings(): array
    {
        return [
            'nis',
            'nisn',
            'name',
            'email',
            'gender',
            'phone',
            'class_name',
            'parent_id',
            'template_version',
        ];
    }

    public function array(): array
    {
        return [
            // Example row
            ['20240001', '1234567890', 'Nama Siswa Contoh', 'siswa@example.com', 'L', '081234567890', 'X RPL 1', '', 'v1.0'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,  // nis
            'B' => 15,  // nisn
            'C' => 30,  // name
            'D' => 30,  // email
            'E' => 10,  // gender (L/P)
            'F' => 18,  // phone
            'G' => 20,  // class_name
            'H' => 15,  // parent_id
            'I' => 18,  // template_version
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        // Style the header row
        $sheet->getStyle('A1:I1')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Style the example row
        $sheet->getStyle('A2:I2')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
        ]);

        // Add notes row 1 - general
        $sheet->setCellValue('A3', 'CATATAN:');
        $sheet->setCellValue('B3', 'Kolom gender diisi L (Laki-laki) atau P (Perempuan). Kolom class_name harus sesuai nama kelas yang sudah ada. Kolom parent_id diisi ID orang tua dari sheet "Data Orang Tua/Wali" di file ini (opsional).');
        $sheet->mergeCells('B3:I3');
        $sheet->getStyle('A3:I3')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC'],
            ],
            'font' => ['italic' => true, 'size' => 9],
        ]);

        // Add notes row 2 - password info
        $sheet->setCellValue('A4', 'PASSWORD:');
        $sheet->setCellValue('B4', 'Password login siswa otomatis dibuat dari NISN + Tahun Masuk (kolom tahun_masuk di sistem). Pastikan kolom nisn diisi dengan benar dan lengkap untuk keperluan ini.');
        $sheet->mergeCells('B4:I4');
        $sheet->getStyle('A4:I4')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2EFDA'],
            ],
            'font' => ['italic' => true, 'size' => 9],
        ]);

        return [];
    }
}