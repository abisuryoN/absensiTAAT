<?php

namespace App\Exports;

use App\Models\StudentParent;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ParentReferenceExport implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Referensi Orang Tua';
    }

    public function query()
    {
        return StudentParent::query()->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Lengkap',
            'NIK',
            'No. HP Utama',
            'No. HP Cadangan',
            'Alamat',
        ];
    }

    public function map($parent): array
    {
        return [
            $parent->id,
            $parent->name,
            $parent->nik,
            $parent->phone,
            $parent->phone_secondary,
            $parent->address,
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 35,
            'C' => 22,
            'D' => 18,
            'E' => 18,
            'F' => 40,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A1:F1')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        return [];
    }
}