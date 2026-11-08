<?php

namespace App\Exports\Parent;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ParentDailyAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected Collection $attendances;

    public function __construct(Collection $attendances)
    {
        $this->attendances = $attendances;
    }

    public function collection(): Collection
    {
        return $this->attendances;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Siswa',
            'Kelas',
            'Jam Masuk',
            'Status',
            'Keterangan',
        ];
    }

    public function map($attendance): array
    {
        $statusLabel = match ($attendance->status) {
            'hadir' => 'Hadir',
            'terlambat' => 'Terlambat',
            'izin' => 'Izin',
            'sakit' => 'Sakit',
            'tidak_hadir' => 'Alpha',
            default => ucfirst($attendance->status),
        };

        return [
            $attendance->date?->format('d/m/Y') ?? '-',
            $attendance->student->name ?? '-',
            $attendance->student->schoolClass->name ?? '-',
            $attendance->time_in?->format('H:i') ?? '-',
            $statusLabel,
            $attendance->notes ?? '-',
        ];
    }

    public function title(): string
    {
        return 'Rekap Harian';
    }
}
