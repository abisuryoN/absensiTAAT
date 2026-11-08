<?php

namespace Tests\Traits;

use App\Models\AcademicYear;
use App\Models\Major;
use App\Models\Semester;
use App\Models\User;
use App\Models\StudentParent;
use App\Models\Student;
use App\Models\SchoolClass;

trait CreatesTestData
{
    protected function createTestMasterData(): void
    {
        // Create Academic Year
        AcademicYear::create([
            'id' => 1,
            'name' => '2026/2027',
            'start_date' => '2026-07-01',
            'end_date' => '2027-06-30',
            'is_active' => true,
        ]);

        // Create Major
        Major::create([
            'id' => 1,
            'name' => 'IPA',
            'code' => 'IPA',
        ]);

        // Create Semester
        Semester::create([
            'id' => 1,
            'academic_year_id' => 1,
            'semester_number' => 1,
            'name' => 'Ganjil',
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_active' => true,
        ]);
    }

    protected function createParentWithStudent(string $parentName, string $parentNik, string $studentName, string $studentNis, string $barcodeId): array
    {
        // Create Parent User
        $parentUser = User::create([
            'name' => $parentName,
            'email' => $parentNik . '@test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
        $parentUser->assignRole('parent');

        // Create Parent Record
        $parent = StudentParent::create([
            'user_id' => $parentUser->id,
            'name' => $parentName,
            'nik' => $parentNik,
            'phone' => '081234567890',
            'is_active' => true,
        ]);

        // Create Student User
        $studentUser = User::create([
            'name' => $studentName,
            'email' => $studentNis . '@student.test.com',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        // Create or get School Class
        $class = SchoolClass::firstOrCreate(
            ['name' => 'X-1'],
            [
                'academic_year_id' => 1,
                'major_id' => 1,
                'grade_level' => 10,
                'is_active' => true,
            ]
        );

        // Create Student Record
        $student = Student::create([
            'user_id' => $studentUser->id,
            'parent_id' => $parent->id,
            'class_id' => $class->id,
            'nis' => $studentNis,
            'name' => $studentName,
            'gender' => 'L',
            'barcode_id' => $barcodeId,
            'is_active' => true,
        ]);

        return [
            'parent_user' => $parentUser,
            'parent' => $parent,
            'student_user' => $studentUser,
            'student' => $student,
            'class' => $class,
        ];
    }
}
