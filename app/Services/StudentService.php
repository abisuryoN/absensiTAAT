<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use App\Models\ClassStudentHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class StudentService
{
    public function getAll(array $filters = [])
    {
        $query = Student::with(['user', 'class', 'parent']);

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('nis', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('nisn', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['class_id'])) {
            $query->where('class_id', $filters['class_id']);
        }

        if (!empty($filters['grade_level'])) {
            $query->whereHas('class', function ($q) use ($filters) {
                $q->where('grade_level', $filters['grade_level']);
            });
        }

        if (!empty($filters['major_id'])) {
            $query->whereHas('class', function ($q) use ($filters) {
                $q->where('major_id', $filters['major_id']);
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->paginate(15);
    }

    public function store(array $data): Student
    {
        return DB::transaction(function () use ($data) {
            // Create user account
            $password = !empty($data['password']) ? $data['password'] : ($data['nis'] ?: 'siswa123');
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($password),
                'is_active' => $data['is_active'] ?? true,
            ]);

            $user->assignRole('siswa');

            if (!empty($data['photo'])) {
                $data['photo'] = $data['photo']->store('students/photos', 'public');
            }

            // Generate unique barcode_id (could just be their NIS or random unique)
            $barcodeId = $data['barcode_id'] ?? $data['nis'];

            $student = Student::create([
                'user_id' => $user->id,
                'parent_id' => $data['parent_id'] ?? null,
                'class_id' => $data['class_id'],
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? null,
                'name' => $data['name'],
                'gender' => $data['gender'],
                'phone' => $data['phone'] ?? null,
                'birth_date' => $data['birth_date'] ?? null,
                'birth_place' => $data['birth_place'] ?? null,
                'address' => $data['address'] ?? null,
                'photo' => $data['photo'] ?? null,
                'barcode_id' => $barcodeId,
                'is_active' => $data['is_active'] ?? true,
            ]);

            // Save history of student class
            ClassStudentHistory::create([
                'student_id' => $student->id,
                'class_id' => $student->class_id,
                'academic_year_id' => \App\Models\AcademicYear::active()->first()?->id ?? 1,
            ]);

            ActivityLogService::logCreate($student, "Menambahkan Siswa: {$student->name}");

            return $student;
        });
    }

    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {
            $original = $student->getAttributes();
            $user = $student->user;
            $oldClassId = $student->class_id;

            // Handle photo upload
            if (!empty($data['photo'])) {
                if ($student->photo) {
                    Storage::disk('public')->delete($student->photo);
                }
                $data['photo'] = $data['photo']->store('students/photos', 'public');
            }

            // Update user details
            $userData = [
                'name' => $data['name'],
                'email' => $data['email'],
            ];

            if (isset($data['is_active'])) {
                $userData['is_active'] = $data['is_active'];
            }

            if (!empty($data['password'])) {
                $userData['password'] = Hash::make($data['password']);
            }

            $user->update($userData);

            // Generate unique barcode_id
            $barcodeId = $data['barcode_id'] ?? $data['nis'];

            // Update student
            $student->update([
                'parent_id' => $data['parent_id'] ?? $student->parent_id,
                'class_id' => $data['class_id'],
                'nis' => $data['nis'],
                'nisn' => $data['nisn'] ?? $student->nisn,
                'name' => $data['name'],
                'gender' => $data['gender'],
                'phone' => $data['phone'] ?? $student->phone,
                'birth_date' => $data['birth_date'] ?? $student->birth_date,
                'birth_place' => $data['birth_place'] ?? $student->birth_place,
                'address' => $data['address'] ?? $student->address,
                'photo' => $data['photo'] ?? $student->photo,
                'barcode_id' => $barcodeId,
                'is_active' => $data['is_active'] ?? $student->is_active,
            ]);

            // Save history of student class if changed
            if ($oldClassId != $student->class_id) {
                ClassStudentHistory::create([
                    'student_id' => $student->id,
                    'class_id' => $student->class_id,
                    'academic_year_id' => \App\Models\AcademicYear::active()->first()?->id ?? 1,
                ]);
            }

            ActivityLogService::logUpdate($student, $original, "Mengubah Siswa: {$student->name}");

            return $student;
        });
    }

    public function delete(Student $student): void
    {
        DB::transaction(function () use ($student) {
            $user = $student->user;

            ActivityLogService::logDelete($student, "Menghapus Siswa: {$student->name}");
            $student->delete();

            if ($user) {
                $user->delete();
            }
        });
    }
}
