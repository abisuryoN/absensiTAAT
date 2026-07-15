<?php

namespace App\Services;

use App\Models\Major;
use Illuminate\Support\Facades\DB;

class MajorService
{
    public function getAll(array $filters = [])
    {
        $query = Major::withCount('classes');

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

    public function store(array $data): Major
    {
        return DB::transaction(function () use ($data) {
            $major = Major::create($data);
            ActivityLogService::logCreate($major, "Menambahkan Jurusan: {$major->name}");
            return $major;
        });
    }

    public function update(Major $major, array $data): Major
    {
        return DB::transaction(function () use ($major, $data) {
            $original = $major->getAttributes();
            $major->update($data);
            ActivityLogService::logUpdate($major, $original, "Mengubah Jurusan: {$major->name}");
            return $major;
        });
    }

    public function delete(Major $major): void
    {
        DB::transaction(function () use ($major) {
            if ($major->classes()->count() > 0) {
                throw new \Exception('Jurusan tidak dapat dihapus karena masih memiliki data kelas.');
            }
            ActivityLogService::logDelete($major, "Menghapus Jurusan: {$major->name}");
            $major->delete();
        });
    }
}
