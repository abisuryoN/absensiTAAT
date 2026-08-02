<?php

namespace App\Services;

use App\Models\Holiday;
use App\Models\SchoolHoliday;
use App\Models\AcademicYear;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class HolidayService
{
    protected LiburApiService $liburApiService;
    protected DateTimeService $dateTimeService;

    public function __construct(LiburApiService $liburApiService, DateTimeService $dateTimeService)
    {
        $this->liburApiService  = $liburApiService;
        $this->dateTimeService  = $dateTimeService;
    }

    // =========================================================================
    // CENTRALIZED HOLIDAY CHECK — gunakan ini di semua controller & service
    // =========================================================================

    /**
     * Cek apakah tanggal tertentu adalah hari libur.
     * Return array: ['is_holiday' => bool, 'reason' => string|null]
     * Cache per tanggal selama 24 jam.
     */
    public function isHoliday(Carbon $date): array
    {
        $dateStr  = $date->format('Y-m-d');
        $cacheKey = "holiday_{$dateStr}";

        return Cache::remember($cacheKey, now()->addHours(24), function () use ($date, $dateStr) {
            // 1. Sabtu & Minggu — runtime, tidak perlu DB
            if ($date->isSaturday()) {
                return ['is_holiday' => true, 'reason' => 'Sabtu (akhir pekan)'];
            }
            if ($date->isSunday()) {
                return ['is_holiday' => true, 'reason' => 'Minggu (akhir pekan)'];
            }

            // 2. Libur Sekolah Khusus (school_holidays) — is_active = true
            $schoolHoliday = SchoolHoliday::where('holiday_date', $dateStr)
                ->where('is_active', true)
                ->first();
            if ($schoolHoliday) {
                return ['is_holiday' => true, 'reason' => $schoolHoliday->title];
            }

            // 3. Hari Libur Nasional (tabel holidays) — kecuali Sabtu/Minggu yg sudah ditangani
            $nationalHoliday = Holiday::where('date', $dateStr)
                ->whereNotIn('name', ['Sabtu', 'Minggu'])
                ->first();
            if ($nationalHoliday) {
                return ['is_holiday' => true, 'reason' => $nationalHoliday->name];
            }

            return ['is_holiday' => false, 'reason' => null];
        });
    }

    /**
     * Shorthand: return true/false saja.
     */
    public function isHolidayDate(Carbon $date): bool
    {
        return $this->isHoliday($date)['is_holiday'];
    }

    /**
     * Hapus cache untuk tanggal tertentu atau semua cache hari libur.
     * Panggil setelah add/edit/delete school_holidays atau sync hari libur.
     */
    public function clearCache(?Carbon $date = null): void
    {
        if ($date) {
            Cache::forget("holiday_{$date->format('Y-m-d')}");
        } else {
            // Clear cache for 365 days around today (using simulated today)
            $today = $this->dateTimeService->today();
            for ($i = -365; $i <= 365; $i++) {
                Cache::forget("holiday_{$today->copy()->addDays($i)->format('Y-m-d')}");
            }
        }
    }

    // =========================================================================
    // SCHOOL HOLIDAY CRUD
    // =========================================================================

    /**
     * Get all school holidays with optional filters.
     */
    public function getAllSchoolHolidays(array $filters = [], int $perPage = 15)
    {
        $query = SchoolHoliday::with('creator');

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['year'])) {
            $query->whereYear('holiday_date', $filters['year']);
        }

        if (!empty($filters['month'])) {
            $query->whereMonth('holiday_date', $filters['month']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        return $query->orderByDesc('holiday_date')->paginate($perPage);
    }

    /**
     * Store a new school holiday.
     */
    public function storeSchoolHoliday(array $data): SchoolHoliday
    {
        return DB::transaction(function () use ($data) {
            $holiday = SchoolHoliday::create($data);
            $this->clearCache(Carbon::parse($holiday->holiday_date));
            ActivityLogService::logCreate($holiday, "Menambahkan Hari Libur Sekolah: {$holiday->title} pada {$holiday->holiday_date->format('Y-m-d')}");
            return $holiday;
        });
    }

    /**
     * Update an existing school holiday.
     */
    public function updateSchoolHoliday(SchoolHoliday $holiday, array $data): SchoolHoliday
    {
        return DB::transaction(function () use ($holiday, $data) {
            $oldDate  = $holiday->holiday_date->copy();
            $original = $holiday->getAttributes();
            $holiday->update($data);
            $this->clearCache($oldDate);
            $this->clearCache(Carbon::parse($holiday->fresh()->holiday_date));
            ActivityLogService::logUpdate($holiday, $original, "Mengubah Hari Libur Sekolah: {$holiday->title}");
            return $holiday;
        });
    }

    /**
     * Delete a school holiday.
     */
    public function deleteSchoolHoliday(SchoolHoliday $holiday): void
    {
        DB::transaction(function () use ($holiday) {
            $date = $holiday->holiday_date->copy();
            ActivityLogService::logDelete($holiday, "Menghapus Hari Libur Sekolah: {$holiday->title}");
            $holiday->delete();
            $this->clearCache($date);
        });
    }

    // =========================================================================
    // NATIONAL HOLIDAY (existing functionality preserved)
    // =========================================================================

    public function getAll(array $filters = [], $perPage = 15)
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

        return $query->orderByDesc('date')->paginate($perPage);
    }

    public function store(array $data): Holiday
    {
        return DB::transaction(function () use ($data) {
            $holiday = Holiday::create($data);
            $this->clearCache(Carbon::parse($holiday->date));
            ActivityLogService::logCreate($holiday, "Menambahkan Hari Libur: {$holiday->name} pada {$holiday->date->format('Y-m-d')}");
            return $holiday;
        });
    }

    public function update(Holiday $holiday, array $data): Holiday
    {
        return DB::transaction(function () use ($holiday, $data) {
            $oldDate  = Carbon::parse($holiday->date);
            $original = $holiday->getAttributes();
            $holiday->update($data);
            $this->clearCache($oldDate);
            $this->clearCache(Carbon::parse($holiday->date));
            ActivityLogService::logUpdate($holiday, $original, "Mengubah Hari Libur: {$holiday->name}");
            return $holiday;
        });
    }

    public function delete(Holiday $holiday): void
    {
        DB::transaction(function () use ($holiday) {
            $date = Carbon::parse($holiday->date);
            ActivityLogService::logDelete($holiday, "Menghapus Hari Libur: {$holiday->name}");
            $holiday->delete();
            $this->clearCache($date);
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
            $endYear   = Carbon::parse($academicYear->end_date)->year;

            $apiHolidays = [];
            $apiFailed   = true;

            for ($y = $startYear; $y <= $endYear; $y++) {
                $result = $this->liburApiService->fetchHolidaysRaw($y);
                if ($result !== null) {
                    $apiFailed   = false;
                    $apiHolidays = array_merge($apiHolidays, $result);
                }
            }

            if ($apiFailed) {
                return [
                    'success' => false,
                    'message' => 'Tidak dapat mengambil data dari API',
                    'synced'  => 0,
                ];
            }

            if (empty($apiHolidays)) {
                return [
                    'success' => true,
                    'message' => 'Tidak ada data hari libur yang cocok untuk tahun ajaran ini',
                    'synced'  => 0,
                    'skipped' => 0,
                ];
            }

            $synced  = 0;
            $skipped = 0;

            foreach ($apiHolidays as $apiHoliday) {
                $holidayData = $this->liburApiService->parseHolidayData($apiHoliday);
                $date        = $holidayData['date'];

                if ($date < $academicYear->start_date || $date > $academicYear->end_date) {
                    continue;
                }

                $exists = Holiday::where('academic_year_id', $academicYearId)
                    ->where('date', $date)
                    ->exists();

                if (!$exists) {
                    Holiday::create([
                        'academic_year_id' => $academicYearId,
                        'name'             => $holidayData['name'],
                        'date'             => $holidayData['date'],
                        'type'             => $holidayData['type'],
                        'description'      => $holidayData['description'],
                    ]);
                    $this->clearCache(Carbon::parse($date));
                    $synced++;
                } else {
                    $skipped++;
                }
            }

            $weekendsSynced = $this->syncWeekends($academicYearId);
            $synced        += $weekendsSynced;

            ActivityLogService::log(
                'sync',
                "Sinkronisasi hari libur dari API: {$synced} data ditambahkan, {$skipped} data dilewati untuk tahun ajaran {$academicYear->name}",
                null
            );

            return [
                'success' => true,
                'message' => "Berhasil menyinkronkan {$synced} hari libur",
                'synced'  => $synced,
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
        $start   = Carbon::parse($academicYear->start_date);
        $end     = Carbon::parse($academicYear->end_date);
        $synced  = 0;
        $current = $start->copy();

        while ($current->lte($end)) {
            if ($current->isSaturday() || $current->isSunday()) {
                $dayName = $current->isSaturday() ? 'Sabtu' : 'Minggu';

                $exists = Holiday::where('academic_year_id', $academicYearId)
                    ->where('date', $current->format('Y-m-d'))
                    ->exists();

                if (!$exists) {
                    Holiday::create([
                        'academic_year_id' => $academicYearId,
                        'name'             => $dayName,
                        'date'             => $current->format('Y-m-d'),
                        'type'             => 'school',
                        'description'      => 'Akhir pekan',
                    ]);
                    $synced++;
                }
            }
            $current->addDay();
        }

        return $synced;
    }
}
