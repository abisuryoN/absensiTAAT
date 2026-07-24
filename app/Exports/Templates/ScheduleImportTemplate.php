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

class ScheduleImportTemplate implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Template Import Jadwal';
    }

    public function headings(): array
    {
        return [
            'teacher_email',
            'subject_code',
            'class_name',
            'day',
            'start_time',
            'end_time',
            'room',
            'template_version',
        ];
    }

    public function array(): array
    {
        return [
            ['guru@example.com', 'MTK10', 'X RPL 1', 'Senin', '07:00', '08:30', 'R.101', 'v1.0']
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 30, // teacher_email
            'B' => 15, // subject_code
            'C' => 20, // class_name
            'D' => 15, // day
            'E' => 15, // start_time
            'F' => 15, // end_time
            'G' => 15, // room
            'H' => 18, // template_version
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:H1')->applyFromArray([
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

        $sheet->getStyle('A2:H2')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'D9E1F2'],
            ],
        ]);

        return [];
    }
}