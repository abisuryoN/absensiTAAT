<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class AttendanceGateExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $collection;
    protected bool $isTidakHadir;
    protected int $counter = 1;

    public function __construct($collection, bool $isTidakHadir = false)
    {
        $this->collection   = $collection;
        $this->isTidakHadir = $isTidakHadir;
    }

    public function title(): string
    {
        return 'Rekap Absensi';
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
            'Jurusan',
            'Jam Masuk',
            'Status',
            'Metode',
            'Keterangan / Catatan',
            'Petugas / Scanner',
        ];
    }

    public function map($row): array
    {
        if ($this->isTidakHadir) {
            // Pseudo-object row for "tidak hadir" students
            return [
                $this->counter++,
                $row->date instanceof \Carbon\Carbon ? $row->date->format('d/m/Y') : $row->date,
                $row->date instanceof \Carbon\Carbon ? $row->date->translatedFormat('l') : '-',
                $row->student->nis ?? '-',
                $row->student->name ?? '-',
                $row->student->class->name ?? '-',
                $row->student->class->major->name ?? '-',
                '-',
                'Tidak Hadir',
                '-',
                '-',
                '-',
            ];
        }

        $statusMap = [
            'hadir'     => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin'      => 'Izin',
            'sakit'     => 'Sakit',
            'alpha'     => 'Alpha',
        ];

        $methodMap = [
            'barcode' => 'Barcode',
            'qr_code' => 'QR Code',
            'manual'  => 'Manual',
        ];

        return [
            $this->counter++,
            $row->date instanceof \Carbon\Carbon ? $row->date->format('d/m/Y') : ($row->date ? $row->date : '-'),
            $row->date instanceof \Carbon\Carbon ? $row->date->translatedFormat('l') : '-',
            $row->student->nis ?? '-',
            $row->student->name ?? '-',
            $row->student->class->name ?? '-',
            $row->student->class->major->name ?? '-',
            $row->time_in ? substr($row->time_in, 0, 5) . ' WIB' : '-',
            $statusMap[$row->status] ?? ucfirst($row->status),
            $methodMap[$row->method] ?? ucfirst($row->method ?? '-'),
            $row->note ?: '-',
            $row->scanner->name ?? 'System',
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            // Header row styling
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF2563EB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}