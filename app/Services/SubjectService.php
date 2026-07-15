<?php

namespace App\Services;

use App\Models\Subject;
use Illuminate\Support\Facades\DB;

class SubjectService
{
    public function getAll(array $filters = [])
    {
        $query = Subject::query();

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('code', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->paginate(15);
    }

    public function store(array $data): Subject
    {
        return DB::transaction(function () use ($data) {
            $subject = Subject::create($data);
            ActivityLogService::logCreate($subject, "Menambahkan Mata Pelajaran: {$subject->name}");
            return $subject;
        });
    }

    public function update(Subject $subject, array $data): Subject
    {
        return DB::transaction(function () use ($subject, $data) {
            $original = $subject->getAttributes();
            $subject->update($data);
            ActivityLogService::logUpdate($subject, $original, "Mengubah Mata Pelajaran: {$subject->name}");
            return $subject;
        });
    }

    public function delete(Subject $subject): void
    {
        DB::transaction(function () use ($subject) {
            if ($subject->schedules()->count() > 0) {
                throw new \Exception('Mata Pelajaran tidak dapat dihapus karena masih memiliki data jadwal.');
            }
            ActivityLogService::logDelete($subject, "Menghapus Mata Pelajaran: {$subject->name}");
            $subject->delete();
        });
    }
}
