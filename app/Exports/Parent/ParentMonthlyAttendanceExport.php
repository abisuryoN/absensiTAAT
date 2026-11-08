<?php

namespace App\Exports\Parent;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class ParentMonthlyAttendanceExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected Collection $months;

    public function __construct(Collection $months)
    {
        $this->months = $months;
    }

    public function collection(): Collection
    {
        return $this->months;
    }

    public function headings(): array
    {
        return [
            'Bulan',
            'Hadir',
            'Terlambat',
            'Izin',
            'Sakit',
            'Alpha',
            'Total',
        ];
    }

    public function map($month): array
    {
        return [
            $month['month_label'],
            $month['hadir'],
            $month['terlambat'],
            $month['izin'],
            $month['sakit'],
            $month['tidak_hadir'],
            $month['total'],
        ];
    }

    public function title(): string
    {
        return 'Rekap Bulanan';
    }
}
