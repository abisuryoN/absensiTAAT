<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class AttendanceSubjectExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
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

        return [
            $this->counter++,
            $attendance && $attendance->date ? $attendance->date->format('Y-m-d') : '-',
            $attendance && $attendance->date ? $attendance->date->translatedFormat('l') : '-',
            $schedule ? substr($schedule->start_time, 0, 5) . ' - ' . substr($schedule->end_time, 0, 5) : '-',
            $schedule->class->name ?? '-',
            $schedule->subject->name ?? '-',
            $schedule->teacher->name ?? '-',
            $row->student->nis ?? '-',
            $row->student->name ?? '-',
            ucfirst($row->status),
            $row->note ?? '-',
        ];
    }
}
