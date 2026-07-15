<?php

namespace App\Services;

use App\Models\SchoolClass;
use Illuminate\Support\Facades\DB;

class SchoolClassService
{
    public function getAll(array $filters = [])
    {
        $query = SchoolClass::with(['academicYear', 'major', 'homeroomTeacher'])
            ->withCount('students');

        if (!empty($filters['search'])) {
            $query->where('name', 'like', '%' . $filters['search'] . '%');
        }

        if (!empty($filters['academic_year_id'])) {
            $query->where('academic_year_id', $filters['academic_year_id']);
        }

        if (!empty($filters['major_id'])) {
            $query->where('major_id', $filters['major_id']);
        }

        if (!empty($filters['grade_level'])) {
            $query->where('grade_level', $filters['grade_level']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('grade_level')->orderBy('name')->paginate(15);
    }

    public function store(array $data): SchoolClass
    {
        return DB::transaction(function () use ($data) {
            $class = SchoolClass::create($data);
            ActivityLogService::logCreate($class, "Menambahkan Kelas: {$class->name}");
            return $class;
        });
    }

    public function update(SchoolClass $class, array $data): SchoolClass
    {
        return DB::transaction(function () use ($class, $data) {
            $original = $class->getAttributes();
            $class->update($data);
            ActivityLogService::logUpdate($class, $original, "Mengubah Kelas: {$class->name}");
            return $class;
        });
    }

    public function delete(SchoolClass $class): void
    {
        DB::transaction(function () use ($class) {
            if ($class->students()->count() > 0) {
                throw new \Exception('Kelas tidak dapat dihapus karena masih memiliki data siswa.');
            }
            if ($class->schedules()->count() > 0) {
                throw new \Exception('Kelas tidak dapat dihapus karena masih memiliki data jadwal.');
            }
            ActivityLogService::logDelete($class, "Menghapus Kelas: {$class->name}");
            $class->delete();
        });
    }
}
