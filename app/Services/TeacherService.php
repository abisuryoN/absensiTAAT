<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class TeacherService
{
    public function getAll(array $filters = [])
    {
        $query = Teacher::with('user');

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('nip', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('phone', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->paginate(15)->withQueryString();
    }

    public function store(array $data): Teacher
    {
        return DB::transaction(function () use ($data) {
            // Create user account
            if (empty($data['password'])) {
                $tempTeacher = new Teacher([
                    'nip'               => $data['nip'] ?? null,
                    'tahun_masuk_kerja' => isset($data['tahun_masuk_kerja']) && $data['tahun_masuk_kerja'] !== ''
                        ? (int) $data['tahun_masuk_kerja'] : null,
                ]);
                $password = (new \App\Services\PasswordGeneratorService())->generateForTeacher($tempTeacher);
            } else {
                $password = $data['password'];
            }
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($password),
                'is_active' => $data['is_active'] ?? true,
            ]);

            $user->assignRole('guru');

            if (!empty($data['photo'])) {
                $data['photo'] = $data['photo']->store('teachers/photos', 'public');
            }

            $teacher = Teacher::create([
                'user_id'           => $user->id,
                'nip'               => $data['nip'] ?? null,
                'nuptk'             => $data['nuptk'] ?? null,
                'name'              => $data['name'],
                'phone'             => $data['phone'] ?? null,
                'gender'            => $data['gender'],
                'address'           => $data['address'] ?? null,
                'photo'             => $data['photo'] ?? null,
                'is_active'         => $data['is_active'] ?? true,
                'tahun_masuk_kerja' => isset($data['tahun_masuk_kerja']) && $data['tahun_masuk_kerja'] !== ''
                    ? (int) $data['tahun_masuk_kerja'] : null,
            ]);

            // Sync subjects if provided
            if (!empty($data['subjects'])) {
                $teacher->subjects()->syncWithPivotValues($data['subjects'], [
                    'academic_year_id' => $data['academic_year_id'] ?? \App\Models\AcademicYear::active()->first()?->id
                ]);
            }

            ActivityLogService::logCreate($teacher, "Menambahkan Guru: {$teacher->name} (User ID: {$user->id})");

            return $teacher;
        });
    }

    public function update(Teacher $teacher, array $data): Teacher
    {
        $oldPhoto = $teacher->photo;
        $newPhoto = null;

        if (!empty($data['photo'])) {
            $newPhoto = $data['photo']->store('teachers/photos', 'public');
        }

        try {
            $updated = DB::transaction(function () use ($teacher, $data, $newPhoto) {
                $original = $teacher->getAttributes();
                $user = $teacher->user;

                // Update user details
                $userData = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                ];
                if (isset($data['is_active'])) $userData['is_active'] = $data['is_active'];
                if (!empty($data['password'])) $userData['password'] = Hash::make($data['password']);
                $user->update($userData);

                // Update teacher details (exclude photo, handle separately)
                $teacherData = array_intersect_key($data, array_flip([
                    'nip', 'nuptk', 'name', 'phone', 'gender', 'address', 'is_active', 'tahun_masuk_kerja'
                ]));

                // Only update photo if new file was uploaded
                if ($newPhoto) {
                    $teacherData['photo'] = $newPhoto;
                }

                $teacher->update($teacherData);

                if (isset($data['tahun_masuk_kerja'])) {
                    $teacher->tahun_masuk_kerja = $data['tahun_masuk_kerja'] !== '' ? (int) $data['tahun_masuk_kerja'] : null;
                    $teacher->save();
                }

                // Sync subjects if provided
                if (isset($data['subjects'])) {
                    $teacher->subjects()->syncWithPivotValues($data['subjects'], [
                        'academic_year_id' => $data['academic_year_id'] ?? \App\Models\AcademicYear::active()->first()?->id
                    ]);
                }

                ActivityLogService::logUpdate($teacher, $original, "Mengubah Guru: {$teacher->name}");

                return $teacher;
            });
        } catch (\Throwable $e) {
            if ($newPhoto) {
                Storage::disk('public')->delete($newPhoto);
            }
            throw $e;
        }

        // Delete old photo after successful transaction
        if ($newPhoto && $oldPhoto) {
            try {
                Storage::disk('public')->delete($oldPhoto);
            } catch (\Exception $e) {
                Log::warning("Failed to delete old teacher photo: {$oldPhoto}", ['error' => $e->getMessage()]);
            }
        }

        return $updated;
    }

    public function delete(Teacher $teacher): void
    {
        $oldPhoto = $teacher->photo;

        DB::transaction(function () use ($teacher) {
            // Check relationships
            if ($teacher->classes()->count() > 0) {
                throw new \Exception('Guru tidak dapat dihapus karena bertindak sebagai wali kelas.');
            }
            if ($teacher->schedules()->count() > 0) {
                throw new \Exception('Guru tidak dapat dihapus karena memiliki jadwal mengajar.');
            }

            $user = $teacher->user;

            ActivityLogService::logDelete($teacher, "Menghapus Guru: {$teacher->name}");

            $teacher->delete();

            if ($user) {
                $user->delete();
            }
        });

        // Delete photo after successful transaction
        if ($oldPhoto) {
            Storage::disk('public')->delete($oldPhoto);
        }
    }
}
