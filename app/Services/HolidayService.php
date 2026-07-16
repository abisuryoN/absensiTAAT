<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HolidayService
{
    protected LiburApiService $liburApiService;

    public function __construct(LiburApiService $liburApiService)
    {
        $this->liburApiService = $liburApiService;
    }

    public function getAll(array $filters = [])
    {
        $query = Holiday::with('academicYear')
            // Exclude weekend holidays (Sabtu/Minggu) from table display
            ->whereNotIn('name', ['Sabtu', 'Minggu']);

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        return $query->orderByDesc('date')->paginate(15);
    }

    public function store(array $data): Holiday
    {
        return DB::transaction(function () use ($data) {
            $holiday = Holiday::create($data);
            ActivityLogService::logCreate($holiday, "Menambahkan Hari Libur: {$holiday->name} pada {$holiday->date->format('Y-m-d')}");
            return $holiday;
        });
    }

    public function update(Holiday $holiday, array $data): Holiday
    {
        return DB::transaction(function () use ($holiday, $data) {
            $original = $holiday->getAttributes();
            $holiday->update($data);
            ActivityLogService::logUpdate($holiday, $original, "Mengubah Hari Libur: {$holiday->name}");
            return $holiday;
        });
    }

    public function delete(Holiday $holiday): void
    {
        DB::transaction(function () use ($holiday) {
            ActivityLogService::logDelete($holiday, "Menghapus Hari Libur: {$holiday->name}");
            $holiday->delete();
        });
    }

    /**
     * Sync holidays from API for specific academic year.
     */
    public function syncFromApi(int $academicYearId): array
    {
        return DB::transaction(function () use ($academicYearId) {
            $academicYear = AcademicYear::findOrFail($academicYearId);

            $startYear = Carbon::parse($academicYear->start_date)->year;
            $endYear = Carbon::parse($academicYear->end_date)->year;

            // Fetch holidays untuk semua tahun kalender yang dicakup tahun ajaran ini
            $apiHolidays = [];
            $apiFailed = true; // anggap gagal sampai terbukti minimal 1 fetch sukses

            for ($y = $startYear; $y <= $endYear; $y++) {
                $result = $this->liburApiService->fetchHolidaysRaw($y);
                if ($result !== null) {
                    $apiFailed = false;
                    $apiHolidays = array_merge($apiHolidays, $result);
                }
            }

            if ($apiFailed) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat mengambil data dari API',
                    'synced' => 0,
                ];
            }

            if (empty($apiHolidays)) {
                return [
                    'success' => true,
                    'message' => 'Tidak ada data hari libur yang cocok untuk tahun ajaran ini',
                    'synced' => 0,
                    'skipped' => 0,
                ];
            }

            $synced = 0;
            $skipped = 0;

            foreach ($apiHolidays as $apiHoliday) {
                $holidayData = $this->liburApiService->parseHolidayData($apiHoliday);
                $date = $holidayData['date'];

                // Check if date falls within academic year
                if ($date < $academicYear->start_date || $date > $academicYear->end_date) {
                    continue;
                }

                // Check if holiday already exists for this academic year and date
                $exists = Holiday::where('academic_year_id', $academicYearId)
                    ->where('date', $date)
                    ->exists();

                if (!$exists) {
                    Holiday::create([
                        'academic_year_id' => $academicYearId,
                        'name' => $holidayData['name'],
                        'date' => $holidayData['date'],
                        'type' => $holidayData['type'],
                        'description' => $holidayData['description'],
                    ]);
                    $synced++;
                } else {
                    $skipped++;
                }
            }

            // Add weekend holidays (Saturday and Sunday) for the academic year
            $weekendsSynced = $this->syncWeekends($academicYearId);
            $synced += $weekendsSynced;

            ActivityLogService::log(
                'sync',
                "Sinkronisasi hari libur dari API: {$synced} data ditambahkan, {$skipped} data dilewati untuk tahun ajaran {$academicYear->name}",
                null
            );

            return [
                'success' => true,
                'message' => "Berhasil menyinkronkan {$synced} hari libur",
                'synced' => $synced,
                'skipped' => $skipped,
            ];
        });
    }

    /**
     * Sync weekend holidays (Saturday and Sunday) for academic year.
     */
    protected function syncWeekends(int $academicYearId): int
    {
        $academicYear = AcademicYear::findOrFail($academicYearId);
        $start = Carbon::parse($academicYear->start_date);
        $end = Carbon::parse($academicYear->end_date);
        
        $synced = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            // Check if Saturday or Sunday
            if ($current->isSaturday() || $current->isSunday()) {
                $dayName = $current->isSaturday() ? 'Sabtu' : 'Minggu';
                
                // Check if already exists
                $exists = Holiday::where('academic_year_id', $academicYearId)
                    ->where('date', $current->format('Y-m-d'))
                    ->exists();

                if (!$exists) {
                    Holiday::create([
                        'academic_year_id' => $academicYearId,
                        'name' => $dayName,
                        'date' => $current->format('Y-m-d'),
                        'type' => 'school',
                        'description' => 'Akhir pekan',
                    ]);
                    $synced++;
                }
            }
            $current->addDay();
        }

        return $synced;
    }
}
