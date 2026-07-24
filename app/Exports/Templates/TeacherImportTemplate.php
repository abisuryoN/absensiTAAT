<?php

namespace App\Exports\Templates;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TeacherImportTemplate implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Template Import Guru';
    }

    public function headings(): array
    {
        return [
            'nip',
            'nuptk',
            'name',
            'email',
            'gender',
            'phone',
            'template_version',
        ];
    }

    public function array(): array
    {
        return [
            ['198501012010011002', '1234567890123456', 'Nama Guru Contoh, S.Pd.', 'guru@example.com', 'L', '081234567891', 'v1.0']
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 25, // nip
            'B' => 25, // nuptk
            'C' => 30, // name
            'D' => 30, // email
            'E' => 10, // gender (L/P)
            'F' => 18, // phone
            'G' => 18, // template_version
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->getStyle('A2:G2')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
        ]);

        return [];
    }
}