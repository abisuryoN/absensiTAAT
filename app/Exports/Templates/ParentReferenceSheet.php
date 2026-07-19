<?php

namespace App\Exports\Templates;

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

class ParentReferenceSheet implements FromQuery, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle
{
    public function title(): string
    {
        return 'Data Orang Tua/Wali';
    }

    public function query()
    {
        return StudentParent::query()->orderBy('id');
    }

    public function headings(): array
    {
        return [
            'id',
            'nama_lengkap',
            'nik',
            'no_hp_utama',
            'no_hp_cadangan',
            'alamat',
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
            'A' => 10,   // id
            'B' => 35,   // nama_lengkap
            'C' => 22,   // nik
            'D' => 18,   // no_hp_utama
            'E' => 18,   // no_hp_cadangan
            'F' => 40,   // alamat
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestDataRow();

        // Header styling
        $sheet->getStyle('A1:F1')->applyFromArray([
            'fill' => [
                'fillType'   => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '70AD47'],
            ],
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Freeze header row
        $sheet->freezePane('A2');

        // Auto-filter
        if ($lastRow > 1) {
            $sheet->setAutoFilter('A1:F' . $lastRow);
        }

        return [];
    }
}