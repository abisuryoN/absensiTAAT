<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceGateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $collection;
    protected $counter = 1;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return [
            'No',
            'Tanggal',
            'Hari',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Jam Masuk',
            'Status',
            'Metode',
            'Catatan',
        ];
    }

    /**
     * Map each row to columns.
     */
    public function map($row): array
    {
        return [
            $this->counter++,
            $row->date ? $row->date->format('Y-m-d') : '-',
            $row->date ? $row->date->translatedFormat('l') : '-',
            $row->student->nis ?? '-',
            $row->student->name ?? '-',
            $row->student->class->name ?? '-',
            $row->time_in ?? '-',
            ucfirst($row->status),
            ucfirst($row->method),
            $row->note ?? '-',
        ];
    }
}
