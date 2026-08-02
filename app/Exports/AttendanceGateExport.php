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
            'hadir'      => 'Hadir',
            'terlambat'  => 'Terlambat',
            'izin'       => 'Izin',
            'sakit'      => 'Sakit',
            'tidak_hadir' => 'Tidak Hadir',
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
        $lastRow = $sheet->getHighestRow();
        $lastColumn = $sheet->getHighestColumn();
        
        // Set header row height
        $sheet->getRowDimension(1)->setRowHeight(26);

        // General cell alignment & borders
        $range = 'A1:' . $lastColumn . $lastRow;
        $sheet->getStyle($range)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN)->getColor()->setARGB('FFE2E8F0');
        $sheet->getStyle($range)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

        // Styling data rows
        for ($row = 2; $row <= $lastRow; $row++) {
            // Row height
            $sheet->getRowDimension($row)->setRowHeight(20);

            // Zebra striping
            if ($row % 2 === 0) {
                $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
            }

            // Alignment for specific columns
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // No
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Tanggal
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Hari
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // NIS
            $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Kelas
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Jam Masuk
            $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Metode

            // Color status cells
            $statusVal = strtolower($sheet->getCell("I{$row}")->getValue() ?? '');
            $statusStyle = $sheet->getStyle("I{$row}");
            $statusStyle->getFont()->setBold(true);
            
            if (str_contains($statusVal, 'hadir') && !str_contains($statusVal, 'tidak')) {
                $statusStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD1FAE5');
                $statusStyle->getFont()->getColor()->setARGB('FF065F46');
            } elseif (str_contains($statusVal, 'terlambat')) {
                $statusStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEF3C7');
                $statusStyle->getFont()->getColor()->setARGB('FF92400E');
            } elseif (str_contains($statusVal, 'izin')) {
                $statusStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE0F2FE');
                $statusStyle->getFont()->getColor()->setARGB('FF075985');
            } elseif (str_contains($statusVal, 'sakit')) {
                $statusStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEEF2FF');
                $statusStyle->getFont()->getColor()->setARGB('FF3730A3');
            } elseif (str_contains($statusVal, 'alpha') || str_contains($statusVal, 'tidak hadir')) {
                $statusStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFEE2E2');
                $statusStyle->getFont()->getColor()->setARGB('FF991B1B');
            }
        }

        return [
            // Header row styling
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 10],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1E293B'], // Premium Dark Navy
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}