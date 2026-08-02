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

class AttendanceSubjectExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    protected $collection;
    protected $counter = 1;

    public function __construct($collection)
    {
        $this->collection = $collection;
    }

    public function title(): string
    {
        return 'Rekap Absensi Mapel';
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
            'Jam Pelajaran',
            'Kelas',
            'Mata Pelajaran',
            'Guru Pengajar',
            'NIS',
            'Nama Siswa',
            'Status Kehadiran',
            'Catatan',
        ];
    }

    /**
     * Map each row to columns.
     */
    public function map($row): array
    {
        $attendance = $row->attendanceSubject;
        $schedule = $attendance->schedule ?? null;

        $statusMap = [
            'hadir'      => 'Hadir',
            'izin'       => 'Izin',
            'sakit'      => 'Sakit',
            'alpha'      => 'Alpha',
            'dispensasi' => 'Dispensasi',
        ];

        return [
            $this->counter++,
            $attendance && $attendance->date ? $attendance->date->format('d/m/Y') : '-',
            $attendance && $attendance->date ? $attendance->date->translatedFormat('l') : '-',
            $schedule ? substr($schedule->start_time, 0, 5) . ' - ' . substr($schedule->end_time, 0, 5) : '-',
            $schedule->class->name ?? '-',
            $schedule->subject->name ?? '-',
            $schedule->teacher->name ?? '-',
            $row->student->nis ?? '-',
            $row->student->name ?? '-',
            $statusMap[strtolower($row->status)] ?? ucfirst($row->status),
            $row->note ?? '-',
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
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Jam Pelajaran
            $sheet->getStyle("E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Kelas
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // NIS
            $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Status Kehadiran

            // Color status cells
            $statusVal = strtolower($sheet->getCell("J{$row}")->getValue() ?? '');
            $statusStyle = $sheet->getStyle("J{$row}");
            $statusStyle->getFont()->setBold(true);
            
            if (str_contains($statusVal, 'hadir') && !str_contains($statusVal, 'tidak')) {
                $statusStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD1FAE5');
                $statusStyle->getFont()->getColor()->setARGB('FF065F46');
            } elseif (str_contains($statusVal, 'terlambat') || str_contains($statusVal, 'dispensasi')) {
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
