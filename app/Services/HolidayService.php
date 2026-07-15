<?php

namespace App\Services;

use App\Models\Holiday;
use Illuminate\Support\Facades\DB;

class HolidayService
{
    public function getAll(array $filters = [])
    {
        $query = Holiday::with('academicYear');

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
}
