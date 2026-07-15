<?php

namespace App\Services;

use App\Models\AcademicYear;
use Illuminate\Support\Facades\DB;

class AcademicYearService
{
    public function getAll(array $filters = [])
    {
        $query = AcademicYear::query();

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderByDesc('start_date')->paginate(15);
    }

    public function store(array $data): AcademicYear
    {
        return DB::transaction(function () use ($data) {
            // Jika tahun ajaran baru diset aktif, nonaktifkan yang lain
            if (!empty($data['is_active'])) {
                AcademicYear::where('is_active', true)->update(['is_active' => false]);
            }

            $academicYear = AcademicYear::create($data);

            ActivityLogService::logCreate($academicYear, "Menambahkan Tahun Ajaran: {$academicYear->name}");

            return $academicYear;
        });
    }

    public function update(AcademicYear $academicYear, array $data): AcademicYear
    {
        return DB::transaction(function () use ($academicYear, $data) {
            $original = $academicYear->getAttributes();

            // Jika diset aktif, nonaktifkan yang lain
            if (!empty($data['is_active'])) {
                AcademicYear::where('id', '!=', $academicYear->id)
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            $academicYear->update($data);

            ActivityLogService::logUpdate($academicYear, $original, "Mengubah Tahun Ajaran: {$academicYear->name}");

            return $academicYear;
        });
    }

    public function delete(AcademicYear $academicYear): void
    {
        DB::transaction(function () use ($academicYear) {
            // Cek relasi
            if ($academicYear->semesters()->count() > 0) {
                throw new \Exception('Tahun Ajaran tidak dapat dihapus karena masih memiliki data semester.');
            }
            if ($academicYear->classes()->count() > 0) {
                throw new \Exception('Tahun Ajaran tidak dapat dihapus karena masih memiliki data kelas.');
            }

            ActivityLogService::logDelete($academicYear, "Menghapus Tahun Ajaran: {$academicYear->name}");
            $academicYear->delete();
        });
    }
}
